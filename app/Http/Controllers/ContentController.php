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

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ContentController extends Controller
{
    public function index(Request $request)
    {
        $contents = $request->user()->contents()->with('folder', 'tags')->latest()->paginate(12);

        return view('dashboard.content.index', compact('contents'));
    }

    // menampilkan form untuk mengunggah konten baru
    public function create(Request $request)
    {
        // Ambil semua folder milik pengguna yang sedang login
        $folders = $request->user()->folders()->orderBy('folder_name')->get();

        $tags = Tags::orderBy('name_tag')->get();

        return view('dashboard.content.create', compact('folders', 'tags'));
    }

    // menyimpan folder baru
    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'id_parent' => [
                'nullable',
                Rule::exists('tb_folder', 'id')->where(function ($query) {
                    return $query->where('id_users', Auth::id());
                })
            ],
        ]);

        //  Buat folder baru menggunakan relasi Eloquent
        // 'folders()' adalah relasi 'hasMany' di Model User
        // Ini otomatis mengisi 'user_id' dengan ID user yang sedang login
        $request->user()->folders()->create($validated);

        return back()->with('success', 'Folder created successfully!');
    }

    // menyimpan konten baru (upload)
    public function store(Request $request)
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
            'path_hi_res' => 'required|file|mimes:jpg,jpeg,png|max:10240',
            'visibility_content' => 'required|in:public,private,by_request',
        ]);

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
            'visibility_content' => $validated['visibility_content'],
        ]);

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

        $folders = $request->user()->folders()->orderBy('folder_name')->get();
        $tags = Tags::orderBy('name_tag')->get();

        return view('dashboard.content.edit', compact('content', 'folders', 'tags'));
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
}
