<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use App\Models\Content;
use App\Models\Folder;
use App\Models\Tags;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use phpDocumentor\Reflection\Types\Null_;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $contents = $user->contents()->with('folder', 'tags')->latest()->paginate(12);

        // Kirim data ke view
        return view('dashboard.content.index', compact('contents'));
    }

    // menampilkan form untuk mengunggah konten baru
    public function create(Request $request)
    {
        // Ambil semua folder milik pengguna yang sedang login
        $folders = $request->user()->folders()->whereNull('id_parent')->orderBy('folder_name')->get();

        $tags = Tags::orderBy('name_tag')->get();

        return view('dashboard.content.create', compact('folders', 'tags'));
    }

    // menyimpan konten baru (upload)
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'content_title' => 'required|string|max:255',
            'content_description' => 'nullable|string',
            'id_folder' => [
                'required',
                Rule::exists('tb_folder', 'id')->where(function ($query) {
                    return $query->where('id_users', Auth::id());
                })
            ],
            'name_tag' => 'nullable|array',
            'name_tag.*' => 'exists:tb_tag,id',
            'path_hi_res' => 'required|image|mimes:jpg,jpeg,png|max:10240'
            // 'visibility_content' => 'required|in:public,private,by_request',
        ]);

        //cari folder tujuan
        $folder = Folder::where('id', $validated['id_folder'])
            ->where('id_users', $user->id)
            ->firstOrFail();

        // pakai visibility folder untuk konten
        $visibility = $folder->visibility_folder;

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

        // Tambahkan watermark
        $image->text('Rupaia ©', 10, 10, function ($font) {
            $font->color('#ffffff');
            $font->size(24);
            $font->align('left');
            $font->valign('top');
        });

        // Simpan gambar low-res
        $path_low_res = "content_file/{$id_user}/low_res/{$fileName}";
        Storage::disk('public')->put($path_low_res, (string) $image->encode());

        // Simpan data konten ke database
        $content = Content::create([
            'id_users' => $id_user,
            'id_folder' => $validated['id_folder'],
            'content_title' => $validated['content_title'],
            'content_description' => $validated['content_description'],
            'path_hi_res' => $path_hi_res,
            'path_low_res' => $path_low_res,
            'visibility_content' => $visibility,
        ]);

        // if($id_folder){
        //     $visibility = $folder->visibility_folder;
        // } else {
        //     $visibility = 'public';
        // }

        // Proses dan hubungkan tags
        if (!empty($validated['name_tag'])) {

            // Hubungkan konten ini dengan semua tag ID yang sudah diproses
            // 'sync()' adalah perintah Eloquent untuk relasi Many-to-Many
            // Ini akan otomatis menambah/menghapus data di tabel pivot 'tb_content_tag'
            $content->tags()->sync($validated['name_tag']);
        }

        return redirect()->route('content.index')->with('succes', 'Content uploaded successfully!');
    }

    public function edit(Request $request, Content $content)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update konten ini. Ini akan memanggil ContentPolicy@update
        $this->authorize('update', $content);

        $tags = Tags::orderBy('name_tag')->get();
        $currentFolder = $content->folder;

        return view('dashboard.content.edit', compact('content', 'currentFolder', 'tags'));
    }

    public function update(Request $request, Content $content)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update konten ini. Ini akan memanggil ContentPolicy@update
        $this->authorize('update', $content);

        $validated = $request->validate([
            'content_title' => 'required|string|max:255',
            'content_description' => 'nullable|string',
            'id_folder' => [
                'required',
                Rule::exists('tb_folder', 'id')->where(function ($query) {
                    return $query->where('id_users', Auth::id());
                })
            ],
            'name_tag' => 'required|array',
            'name_tag.*' => 'exists:tb_tag,id',
            'visibility_content' => 'required|in:public,private,by_request',
        ]);

        // $content->content_title = $validated['content_title'];
        // $content->content_description = $validated['content_description'];
        // $content->visibility_content = $content->folder->visibility_folder;

        $content->fill($validated);
        $content->save();

        // 'sync' akan mencocokkan tags di database dengan array dari form
        $content->tags()->sync($request->input('name_tag', [])); //'[]' untuk array kosong jika tidak ada yg dipilih

        return redirect()->route('content.index')->with('success', 'Content updated successfully!');
    }

    public function destroy(Content $content)
    {
        // OTORISASI: Cek apakah user ini boleh menghapus konten ini. Ini akan memanggil ContentPolicy@delete
        $this->authorize('delete', $content);

        // Hapus file dari storage
        Storage::disk('public')->delete([$content->path_hi_res, $content->path_low_res]);

        // Hapus data konten dari database
        $content->delete();

        return redirect()->route('content.index')->with('success', 'Content deleted successfully!');
    }

    public function folderIndex(Request $request, Folder $folder)
    {
        $user = $request->user();

        // $this->authorize('view', $folder);

        $folders = $user->folders()->whereNull('id_parent')->latest()->paginate(12);

        return view('dashboard.folder.index', compact('folders'));
    }

    // menampilkan form untuk menambahkan folder baru
    public function createFolder(Request $request, ?Folder $folder)
    {
        // Ambil semua folder milik pengguna yang sedang login
        $allFolders = $request->user()->folders()->orderBy('folder_name')->get();

        return view('dashboard.folder.create', compact('allFolders'));
    }

    // menyimpan folder root baru
    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'visibility_folder' => 'required|in:public,private,by_request',
            'id_parent' => ['nullable', 'exists:tb_folder,id']
        ]);

        $request->user()->folders()->create($validated);

        return redirect()->route('folder.index')->with('success', 'Folder created successfully!');
    }

    public function editFolder(Request $request, Folder $folder)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update folder ini. Ini akan memanggil FolderPolicy@update
        $this->authorize('update', $folder);

        return view('dashboard.folder.edit', compact('folder'));
    }

    public function updateFolder(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'visibility_folder' => 'required|in:public,private,by_request',
        ]);

        $folderData = [
            'folder_name' => $validated['folder_name'],
            'folder_description' => $validated['folder_description'],
            'visibility_folder' => $validated['visibility_folder']
        ];

        // update data
        $folder->fill($folderData);
        $folder->save();

        return redirect()->route('folder.index')->with('success', 'Folder updated successfully!');
    }

    public function destroyFolder(Folder $folder)
    {
        $this->authorize('delete', $folder);
        // INI AKAN MEMICU SEMUA KEJADIAN:
        // - DB akan menghapus folder ini
        // - DB (via cascade) akan menghapus semua sub-folder
        // - DB (via cascade) akan menghapus semua 'content' di folder ini & sub-folder
        // - Model Event 'deleting' akan dipanggil untuk 
        //   setiap 'content' yang dihapus, dan membersihkan file di storage.
        $folder->delete();

        return redirect()->route('folder.index')->with('success', 'Folder deleted successfully!');
    }

    public function detailFolderIndex(Request $request, ?Folder $folder)
    {
        $user = $request->user();
        $currentFolder = $folder;

        if ($currentFolder) {
            $this->authorize('view', $currentFolder);
        }

        // ambil sub folder
        // Jika di root (null), ambil folder utama (yang 'id_parent'-nya null)
        // Jika di dalam folder, ambil 'children' (anak) dari folder itu
        if ($currentFolder) {
            // di dalam folder ➜ ambil anak-anaknya
            $folders = $currentFolder->children()
                ->orderBy('folder_name')
                ->get();
        } else {
            // root ➜ ambil folder milik user yang parent_id null
            $folders = $user->folders()
                ->whereNull('id_parent')
                ->orderBy('folder_name')
                ->get();
        };

        // $folders = $queryFolders->orderBy('folder_name')->get();

        //ambil konten
        // Jika di root, ambil konten yang 'id_folder'-nya null
        // Jika di dalam folder, ambil 'contents' dari folder itu
        if ($currentFolder) {
            $queryContents = $currentFolder->contents();
        } else {
            $queryContents = $user->contents()->whereNull('id_folder');
        }

        $contents = $queryContents->with('tags')->latest()->paginate(10);

        // Bangun Breadcrumbs
        $breadcrumbs = collect();
        $tempFolder = $currentFolder;
        while ($tempFolder) {
            $breadcrumbs->prepend($tempFolder); // tambahkan ke depan
            $tempFolder = $tempFolder->parent; // mundur satu langkah
        }

        return view('dashboard.folder.detail-folder', compact('contents', 'folders', 'currentFolder', 'breadcrumbs'));
    }

    public function createDetailFolder(Request $request, ?Folder $folder)
    {
        $user = $request->user();
        $parentId = $request->query('id_parent');

        $parentFolder = null;

        if ($parentId) {
            $parentFolder = Folder::where('id', $parentId)
                ->where('id_users', $user->id)
                ->firstOrFail();
        }

        return view('dashboard.folder.detail-folder-create', compact('parentFolder'));
    }

    public function storeDetailFolder(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'visibility_folder' => 'required|in:public,private,by_request',
            'id_parent' => ['nullable', 'exists:tb_folder,id']
        ]);

        $user->folders()->create($validated);

        // Kalau ada parent, balik ke folder tersebut
        if (!empty($validated['id_parent'])) {
            return redirect()
                ->route('detail.folder.show', $validated['id_parent'])
                ->with('success', 'Subfolder berhasil dibuat.');
        }

        // Kalau tidak ada parent → folder di root
        return redirect()
            ->route('folder.index')
            ->with('success', 'Folder root berhasil dibuat.');
    }

    public function createContentDetailFolder(Request $request,)
    {

        $user = $request->user();
        $parentId = $request->query('id_parent');

        $parentFolder = null;

        if ($parentId) {
            $parentFolder = Folder::where('id', $parentId)
                ->where('id_users', $user->id)
                ->firstOrFail();
        }

        $tags = Tags::orderBy('name_tag')->get();

        return view('dashboard.folder.content-detail-folder-create', compact('parentFolder', 'tags'));
    }

    public function storeContentDetailFolder(Request $request)
    {
        $validated = $request->validate([
            'content_title' => 'required|string|max:255',
            'content_description' => 'nullable|string',
            'id_folder' => [
                'required',
                Rule::exists('tb_folder', 'id')->where(function ($query) {
                    return $query->where('id_users', Auth::id());
                })
            ],
            'name_tag' => 'nullable|array',
            'name_tag.*' => 'exists:tb_tag,id',
            'path_hi_res' => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'visibility_content' => 'required|in:public,private,by_request',
        ]);

        $id_user = $request->user()->id;
        $id_folder = $validated['id_folder'];

        //cari folder tujuan
        $folder = Folder::where('id', $id_folder)
            ->where('id_users', $id_user)
            ->firstOrFail();

        // pakai visibility folder untuk konten
        $visibility = $folder->visibility_folder;

        $file = $request->file('path_hi_res');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Simpan file asli
        $path_hi_res = $file->storeAs("content_file/{$id_user}/hi_res", $fileName, 'public');

        // Buat dan simpan versi low-res (low res + watermark)
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        // Resize gambar untuk low-res
        $image->scale(width: 720);

        // Tambahkan watermark
        $image->text('Rupaia ©', 10, 10, function ($font) {
            $font->color('#ffffff');
            $font->size(24);
            $font->align('left');
            $font->valign('top');
        });

        // Simpan gambar low-res
        $path_low_res = "content_file/{$id_user}/low_res/{$fileName}";
        Storage::disk('public')->put($path_low_res, (string) $image->encode());

        // Simpan data konten ke database
        $content = Content::create([
            'id_users' => $id_user,
            'id_folder' => $validated['id_folder'],
            'content_title' => $validated['content_title'],
            'content_description' => $validated['content_description'],
            'path_hi_res' => $path_hi_res,
            'path_low_res' => $path_low_res,
            'visibility_content' => $visibility,
        ]);

        // Proses dan hubungkan tags
        if (!empty($validated['name_tag'])) {

            // Hubungkan konten ini dengan semua tag ID yang sudah diproses
            // 'sync()' adalah perintah Eloquent untuk relasi Many-to-Many
            // Ini akan otomatis menambah/menghapus data di tabel pivot 'tb_content_tag'
            $content->tags()->sync($validated['name_tag']);
        }

        return redirect()->route('detail.folder.show', $validated['id_folder'])->with('succes', 'Content uploaded successfully!');
    }

    public function contentMove(Request $request, Content $content)
    {
        $this->authorize('update', $content);

        $user = $request->user();

        $validated = $request->validate([
            'id_folder' => [
                'required',
                Rule::exists('tb_folder', 'id')->where(function ($query) use ($user) {
                    // pastikan folder tujuan milik user yang sama
                    return $query->where('id_users', $user->id);
                }),
            ],
        ]);

        $destinationFolderId = $validated['id_folder'];

        if ($destinationFolderId) {
            $folder = Folder::where('id', $destinationFolderId)
                ->where('id_users', $user->id)
                ->firstOrFail();

            $content->id_folder = $destinationFolderId;
            $content->visibility_content = $folder->visibility_folder;
        } else {
            $content->id_folder = null;
            $content->visibility_content = 'public';
        }

        // $content->id_folder = $destinationFolderId;

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

    public function folderMove(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $user = $request->user();

        $validated = $request->validate([
            'id_parent' => [
                'nullable',
                Rule::exists('tb_folder', 'id')->where(function ($query) use ($user) {
                    // pastikan folder tujuan milik user yang sama
                    return $query->where('id_users', $user->id);
                }),
            ],
        ]);

        $destinationFolderId = $validated['id_parent'] ?? null;

        // Cegah folder jadi parent dirinya sendiri
        if ($destinationFolderId && $destinationFolderId == $folder->id) {
            return back()->withErrors([
                'id_parent' => 'Folder tidak boleh menjadi parent dirinya sendiri'
            ]);
        }

        if ($destinationFolderId) {
            $parentFolder = Folder::where('id', $destinationFolderId)
                ->where('id_users', $user->id)
                ->firstOrFail();

            $folder->id_parent = $parentFolder->id;
            $folder->save();

            // samakan visibilitas folder, subfolder, dan content
            $folder->updateVisibilityRecursive($parentFolder->visibility_folder);
        } else {
            $folder->id_parent = null;
            $folder->save();
        }

        // $folder->id_parent = $destinationFolderId;

        if ($destinationFolderId) {
            return redirect()
                ->route('detail.folder.show', $destinationFolderId)
                ->with('success', 'Folder moved');
        }

        return redirect()
            ->route('folder.index')
            ->with('success', 'Folder moved to root');
    }
}
