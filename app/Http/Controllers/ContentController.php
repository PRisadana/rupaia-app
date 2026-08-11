<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

use App\Models\Content;
use App\Models\Folder;
use App\Models\Tags;
use App\Models\License;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $contents = $user->contents()->with('folder', 'tags', 'license')->latest()->paginate(12);

        // Kirim data ke view
        return view('dashboard.content.index', compact('contents'));
    }

    // menampilkan form untuk mengunggah konten baru
    public function create(Request $request)
    {
        // Ambil semua folder milik pengguna yang sedang login
        $folders = $request->user()->folders()->with('license')->whereNull('parent_id')->orderBy('folder_name')->get();

        $tags = Tags::orderBy('tag_name')->get();

        $licenses = License::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.content.create', compact('folders', 'tags', 'licenses'));
    }

    // menyimpan konten baru (upload)
    public function store(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->id;

        $validated = $request->validate([
            'content_title' => 'required|string|max:255',
            'content_description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'license_id' => [
                'required',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'sale_type' => 'required|in:multi_sale,single_sale',
            'sale_status' => 'required|in:available,inactive',
            'folder_id' => [
                'required',
                Rule::exists('folders', 'id')->where(function ($query) use ($sellerId) {
                    return $query->where('seller_id', $sellerId);
                })
            ],
            'tag_name' => 'required|array',
            'tag_name.*' => 'exists:tags,id',
            'path_hi_res' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'policy_agreement' => ['accepted'],
            // 'status' => 'required|in:active,deleted,banned',
        ]);

        //cari folder tujuan
        $folder = Folder::where('id', $validated['folder_id'])
            ->where('seller_id', $user->id)
            ->firstOrFail();

        $licenseId = $folder->is_bundle
            ? $folder->license_id
            : $validated['license_id'];

        $saleType = $folder->is_bundle
            ? 'multi_sale'
            : $validated['sale_type'];

        $price = ($folder->is_bundle && ! $folder->allow_individual_sale)
            ? 0
            : ($validated['price'] ?? 0.00);

        if ($folder->is_bundle && $validated['sale_type'] === 'single_sale') {
            return back()
                ->withErrors([
                    'sale_type' => 'Single-sale content cannot be uploaded into a bundle folder.',
                ])
                ->withInput();
        }

        // pakai visibility folder untuk konten
        $visibility = $folder->visibility;

        $file = $request->file('path_hi_res');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $id_user = $request->user()->id;

        // Simpan file asli
        $path_hi_res = $file->storeAs("content_file/{$id_user}/hi_res", $fileName, 'public');

        // Buat dan simpan versi low-res (low res + watermark)
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        // Resize gambar untuk low-res
        $image->scale(width: 720);

        $imageWidth = $image->width();

        $watermarkPath = public_path('aset/watermark.png');

        if (file_exists($watermarkPath)) {
            $watermark = $manager->read($watermarkPath);

            $watermarkWidth = (int) ($imageWidth * 0.4);

            $watermark->scaleDown(width: $watermarkWidth);

            $image->place($watermark, 'center', 0, 0, 50);
        }


        // $image->text('Rupaia ©', $imageWidth / 2, $imageHeight / 2, function ($font) {
        //     $font->color('rgba(255, 255, 255, 0.80)');
        //     $font->size(512);
        //     $font->align('center');
        //     $font->valign('middle');
        // });

        // Simpan gambar low-res
        $path_low_res = "content_file/{$id_user}/low_res/{$fileName}";
        Storage::disk('public')->put($path_low_res, (string) $image->encode());

        // Simpan data konten ke database
        $content = Content::create([
            'seller_id' => $id_user,
            'folder_id' => $validated['folder_id'],
            'content_title' => $validated['content_title'],
            'content_description' => $validated['content_description'],
            'price' => $price,
            'license_id' => $licenseId,
            'sale_type' => $saleType,
            'sale_status' => $validated['sale_status'],
            'path_hi_res' => $path_hi_res,
            'path_low_res' => $path_low_res,
            'visibility' => $visibility,
            'status' => 'active',
        ]);

        // Proses dan hubungkan tags


        // Hubungkan konten ini dengan semua tag ID yang sudah diproses
        // 'sync()' adalah perintah Eloquent untuk relasi Many-to-Many
        // Ini akan otomatis menambah/menghapus data di tabel pivot 'tb_content_tag'
        $content->tags()->sync($validated['tag_name']);


        return redirect()->route('content.index')->with('success', 'Content uploaded successfully!');
    }

    public function edit(Request $request, Content $content)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update konten ini. Ini akan memanggil ContentPolicy@update
        $this->authorize('update', $content);

        $tags = Tags::orderBy('tag_name')->get();
        $currentFolder = $content->folder;

        $licenses = License::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.content.edit', compact('content', 'currentFolder', 'tags', 'licenses'));
    }

    public function update(Request $request, Content $content)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update konten ini. Ini akan memanggil ContentPolicy@update
        $this->authorize('update', $content);

        $validated = $request->validate([
            'content_title' => 'required|string|max:255',
            'content_description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'license_id' => [
                'required',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'sale_type' => 'required|in:multi_sale,single_sale',
            'sale_status' => 'required|in:available,inactive,sold_out',

            'folder_id' => [
                'required',
                Rule::exists('folders', 'id')->where(function ($query) {
                    return $query->where('seller_id', Auth::id());
                })
            ],
            'tag_name' => 'required|array',
            'tag_name.*' => 'exists:tags,id',
            'visibility' => 'required|in:public,private',
            // 'status' => 'required|in:active,deleted,banned',
        ]);

        if ($content->sale_type === 'single_sale' && $content->sale_status === 'sold_out') {
            return back()
                ->withErrors([
                    'sale_status' => 'This single sale content has been sold and cannot be sold again.'
                ])
                ->withInput();
        }

        $folder = Folder::where('id', $validated['folder_id'])
            ->where('seller_id', Auth::id())
            ->firstOrFail();

        if ($folder->is_bundle) {
            $validated['license_id'] = $folder->license_id;
        }

        if ($folder->is_bundle && $validated['sale_type'] === 'single_sale') {
            return back()
                ->withErrors([
                    'sale_type' => 'Single-sale content cannot be placed inside a bundle folder.',
                ])
                ->withInput();
        }

        if ($folder->is_bundle) {
            $validated['license_id'] = $folder->license_id;
            $validated['sale_type'] = 'multi_sale';

            if (! $folder->allow_individual_sale) {
                $validated['price'] = 0;
            }
        }

        $content->fill($validated);
        $content->save();

        // 'sync' akan mencocokkan tags di database dengan array dari form
        $content->tags()->sync($request->input('tag_name', [])); //'[]' untuk array kosong jika tidak ada yg dipilih

        return redirect()->route('content.index')->with('success', 'Content updated successfully!');
    }

    public function destroy(Content $content)
    {
        // OTORISASI: Cek apakah user ini boleh menghapus konten ini. Ini akan memanggil ContentPolicy@delete
        $this->authorize('delete', $content);

        // Hapus data konten dari database
        $content->delete();

        return redirect()->route('content.index')->with('success', 'Content deleted successfully!');
    }

    public function createContentDetailFolder(Request $request,)
    {

        $user = $request->user();
        $licenses = License::where('is_active', true)->orderBy('name')->get();
        $parentId = $request->query('parent_id');

        $parentFolder = null;

        if ($parentId) {
            $parentFolder = Folder::with('license')
                ->where('id', $parentId)
                ->where('seller_id', $user->id)
                ->firstOrFail();
        }

        $tags = Tags::orderBy('tag_name')->get();

        return view('dashboard.folder.content-detail-folder-create', compact('parentFolder', 'tags', 'licenses'));
    }

    public function storeContentDetailFolder(Request $request)
    {
        $validated = $request->validate([
            'content_title' => 'required|string|max:255',
            'content_description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'license_id' => [
                'required',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'sale_type' => 'required|in:multi_sale,single_sale',
            'sale_status' => 'required|in:available,inactive',
            'folder_id' => [
                'required',
                Rule::exists('folders', 'id')->where(function ($query) {
                    return $query->where('seller_id', Auth::id());
                })
            ],
            'tag_name' => 'required|array',
            'tag_name.*' => 'exists:tags,id',
            'path_hi_res' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            // 'visibility' => 'required|in:public,private',
            'policy_agreement' => ['accepted'],
        ]);

        $id_user = $request->user()->id;
        $folder_id = $validated['folder_id'];

        //cari folder tujuan
        $folder = Folder::where('id', $folder_id)
            ->where('seller_id', $id_user)
            ->firstOrFail();

        $licenseId = $folder->is_bundle
            ? $folder->license_id
            : $validated['license_id'];

        $saleType = $folder->is_bundle
            ? 'multi_sale'
            : $validated['sale_type'];

        $price = ($folder->is_bundle && ! $folder->allow_individual_sale)
            ? 0
            : ($validated['price'] ?? 0.00);

        if ($folder->is_bundle && $validated['sale_type'] === 'single_sale') {
            return back()
                ->withErrors([
                    'sale_type' => 'Single-sale content cannot be uploaded into a bundle folder.',
                ])
                ->withInput();
        }

        // pakai visibility folder untuk konten
        $visibility = $folder->visibility;

        $file = $request->file('path_hi_res');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Simpan file asli
        $path_hi_res = $file->storeAs("content_file/{$id_user}/hi_res", $fileName, 'public');

        // Buat dan simpan versi low-res (low res + watermark)
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        // Resize gambar untuk low-res
        $image->scale(width: 720);

        $imageWidth = $image->width();

        $watermarkPath = public_path('aset/watermark.png');

        if (file_exists($watermarkPath)) {
            $watermark = $manager->read($watermarkPath);

            $watermarkWidth = (int) ($imageWidth * 0.4);

            $watermark->scaleDown(width: $watermarkWidth);

            $image->place($watermark, 'center', 0, 0, 50);
        }

        // Simpan gambar low-res
        $path_low_res = "content_file/{$id_user}/low_res/{$fileName}";
        Storage::disk('public')->put($path_low_res, (string) $image->encode());

        // Simpan data konten ke database
        $content = Content::create([
            'seller_id' => $id_user,
            'folder_id' => $validated['folder_id'],
            'content_title' => $validated['content_title'],
            'content_description' => $validated['content_description'],
            'price' => $price,
            'license_id' => $licenseId,
            'path_hi_res' => $path_hi_res,
            'path_low_res' => $path_low_res,
            'visibility' => $visibility,
            'sale_type' => $saleType,
            'sale_status' => $validated['sale_status'],
        ]);

        // Proses dan hubungkan tags


        // Hubungkan konten ini dengan semua tag ID yang sudah diproses
        // 'sync()' adalah perintah Eloquent untuk relasi Many-to-Many
        // Ini akan otomatis menambah/menghapus data di tabel pivot 'tb_content_tag'
        $content->tags()->sync($validated['tag_name']);


        return redirect()->route('detail.folder.show', $validated['folder_id'])->with('success', 'Content uploaded successfully!');
    }

    public function createBatch(Request $request)
    {
        $user = $request->user();

        $folders = $user->folders()
            ->with('license')
            ->whereNull('parent_id')
            ->orderBy('folder_name')
            ->get();

        $tags = Tags::orderBy('tag_name')->get();

        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.content.batch-create', compact(
            'folders',
            'tags',
            'licenses'
        ));
    }

    public function storeBatch(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->id;

        $validated = $request->validate([
            'folder_id' => [
                'required',
                Rule::exists('folders', 'id')->where(function ($query) use ($sellerId) {
                    return $query->where('seller_id', $sellerId);
                }),
            ],
            'images' => ['required', 'array', 'max:50'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'default_description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'license_id' => [
                'nullable',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'sale_type' => ['nullable', 'in:multi_sale,single_sale'],
            'sale_status' => ['required', 'in:available,inactive'],
            'tag_name' => ['required', 'array'],
            'tag_name.*' => ['exists:tags,id'],
            'policy_agreement' => ['accepted'],
        ]);

        $folder = Folder::where('id', $validated['folder_id'])
            ->where('seller_id', $sellerId)
            ->firstOrFail();

        if (! $folder->is_bundle && blank($validated['license_id'])) {
            return back()
                ->withErrors([
                    'license_id' => 'License is required when uploading content to a collection folder.',
                ])
                ->withInput();
        }

        if ($folder->is_bundle && blank($folder->license_id)) {
            return back()
                ->withErrors([
                    'folder_id' => 'This bundle folder does not have a license.',
                ])
                ->withInput();
        }

        $licenseId = $folder->is_bundle
            ? $folder->license_id
            : $validated['license_id'];

        $saleType = $folder->is_bundle
            ? 'multi_sale'
            : ($validated['sale_type'] ?? 'multi_sale');

        $saleStatus = $validated['sale_status'];
        $visibility = $folder->visibility;
        $description = $validated['default_description'] ?? null;
        $price = ($folder->is_bundle && ! $folder->allow_individual_sale)
            ? 0
            : ($validated['price'] ?? 0.00);

        foreach ($request->file('images') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeTitle = str_replace(['_', '-'], ' ', $originalName);
            $contentTitle = ucwords($safeTitle);

            $fileName = uniqid() . '_' . time() . '_' . $file->getClientOriginalName();

            $pathHiRes = $file->storeAs("content_file/{$sellerId}/hi_res", $fileName, 'public');

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            // Resize gambar untuk low-res
            $image->scale(width: 720);

            $imageWidth = $image->width();

            $watermarkPath = public_path('aset/watermark.png');

            if (file_exists($watermarkPath)) {
                $watermark = $manager->read($watermarkPath);

                $watermarkWidth = (int) ($imageWidth * 0.4);

                $watermark->scaleDown(width: $watermarkWidth);

                $image->place($watermark, 'center', 0, 0, 50);
            }

            $pathLowRes = "content_file/{$sellerId}/low_res/{$fileName}";
            Storage::disk('public')->put($pathLowRes, (string) $image->encode());

            $content = Content::create([
                'seller_id' => $sellerId,
                'folder_id' => $folder->id,
                'license_id' => $licenseId,
                'content_title' => $contentTitle,
                'content_description' => $description,
                'price' => $price,
                'sale_type' => $saleType,
                'sale_status' => $saleStatus,
                'path_hi_res' => $pathHiRes,
                'path_low_res' => $pathLowRes,
                'visibility' => $visibility,
                'status' => 'active',
            ]);

            $content->tags()->sync($validated['tag_name']);
        }

        return redirect()
            ->route('content.index')
            ->with('success', count($request->file('images')) . ' content(s) uploaded successfully.');
    }

    public function createBatchFromFolder(Request $request, Folder $folder)
    {
        $user = $request->user();

        if ($folder->seller_id !== $user->id) {
            abort(403);
        }

        $folder->load('license');

        $tags = Tags::orderBy('tag_name')->get();

        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.folder.content-detail-folder-batch-create', compact(
            'folder',
            'tags',
            'licenses'
        ));
    }

    public function storeBatchFromFolder(Request $request, Folder $folder)
    {
        $user = $request->user();
        $sellerId = $user->id;

        if ($folder->seller_id !== $sellerId) {
            abort(403);
        }

        $validated = $request->validate([
            'images' => ['required', 'array', 'max:50'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'default_description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'license_id' => [
                'nullable',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'sale_type' => ['nullable', 'in:multi_sale,single_sale'],
            'sale_status' => ['required', 'in:available,inactive'],
            'tag_name' => ['required', 'array'],
            'tag_name.*' => ['exists:tags,id'],
            'policy_agreement' => ['accepted'],
        ]);

        if (! $folder->is_bundle && blank($validated['license_id'])) {
            return back()
                ->withErrors([
                    'license_id' => 'License is required when uploading content to a collection folder.',
                ])
                ->withInput();
        }

        if ($folder->is_bundle && blank($folder->license_id)) {
            return back()
                ->withErrors([
                    'folder_id' => 'This bundle folder does not have a license.',
                ])
                ->withInput();
        }

        $licenseId = $folder->is_bundle
            ? $folder->license_id
            : $validated['license_id'];

        $saleType = $folder->is_bundle
            ? 'multi_sale'
            : ($validated['sale_type'] ?? 'multi_sale');

        $price = ($folder->is_bundle && ! $folder->allow_individual_sale)
            ? 0
            : ($validated['price'] ?? 0.00);

        $saleStatus = $validated['sale_status'];
        $visibility = $folder->visibility;
        $description = $validated['default_description'] ?? null;

        foreach ($request->file('images') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeTitle = str_replace(['_', '-'], ' ', $originalName);
            $contentTitle = ucwords($safeTitle);

            $fileName = uniqid() . '_' . time() . '_' . $file->getClientOriginalName();

            $pathHiRes = $file->storeAs(
                "content_file/{$sellerId}/hi_res",
                $fileName,
                'public'
            );

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            // Resize gambar untuk low-res
            $image->scale(width: 720);

            $imageWidth = $image->width();

            $watermarkPath = public_path('aset/watermark.png');

            if (file_exists($watermarkPath)) {
                $watermark = $manager->read($watermarkPath);

                $watermarkWidth = (int) ($imageWidth * 0.4);

                $watermark->scaleDown(width: $watermarkWidth);

                $image->place($watermark, 'center', 0, 0, 50);
            }

            $pathLowRes = "content_file/{$sellerId}/low_res/{$fileName}";

            Storage::disk('public')->put(
                $pathLowRes,
                (string) $image->encode()
            );

            $content = Content::create([
                'seller_id' => $sellerId,
                'folder_id' => $folder->id,
                'license_id' => $licenseId,
                'content_title' => $contentTitle,
                'content_description' => $description,
                'price' => $price,
                'sale_type' => $saleType,
                'sale_status' => $saleStatus,
                'path_hi_res' => $pathHiRes,
                'path_low_res' => $pathLowRes,
                'visibility' => $visibility,
                'status' => 'active',
            ]);

            $content->tags()->sync($validated['tag_name']);
        }

        return redirect()
            ->route('detail.folder.show', $folder->id)
            ->with('success', count($request->file('images')) . ' content(s) uploaded successfully.');
    }

    public function contentMove(Request $request, Content $content)
    {
        $this->authorize('update', $content);

        $user = $request->user();

        $validated = $request->validate([
            'folder_id' => [
                'required',
                Rule::exists('folders', 'id')->where(function ($query) use ($user) {
                    // pastikan folder tujuan milik user yang sama
                    return $query->where('seller_id', $user->id);
                }),
            ],
        ]);

        $destinationFolderId = $validated['folder_id'];

        if ($destinationFolderId) {
            $folder = Folder::where('id', $destinationFolderId)
                ->where('seller_id', $user->id)
                ->firstOrFail();

            if ($folder->is_bundle && $content->sale_type === 'single_sale') {
                return back()
                    ->withErrors([
                        'folder_id' => 'Single-sale content cannot be moved into a bundle folder.',
                    ]);
            }

            $content->folder_id = $destinationFolderId;
            $content->visibility = $folder->visibility;

            if ($folder->is_bundle) {
                $content->license_id = $folder->license_id;
            }
        }

        $content->save();

        if ($destinationFolderId) {
            return redirect()
                ->route('detail.folder.show', $destinationFolderId)
                ->with('success', 'Content moved');
        }

        return redirect()
            ->route('folder.index')
            ->with('success', 'Content moved to root');
    }
}
