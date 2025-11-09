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
        $user = $request->user();

        $contents = $user->contents()->with('folder', 'tags')->latest()->paginate(10);

        // // ambil konten
        // // jika $currentFolder ada, ambil konten di dalamnya
        // // jika $currentFolder null, ambil konten yang tidak berada di folder manapun (id_folder = null)
        // $queryContents = $folder ? $folder->contents() : $user->contents()->whereNull('id_folder');

        // $contents = $queryContents->with('folder', 'tags')->latest()->paginate(12);

        // Kirim data ke view
        return view('dashboard.content.index', compact('contents'));
    }

    // menampilkan form untuk mengunggah konten baru
    public function create(Request $request)
    {
        // $currentFolder = $folder;

        // // Otorisasi: Pastikan user hanya mengakses folder miliknya sendiri
        // if ($currentFolder) {
        //     $this->authorize('view', $currentFolder);
        // }

        // Ambil semua folder milik pengguna yang sedang login
        $folders = $request->user()->folders()->orderBy('folder_name')->get();

        $tags = Tags::orderBy('name_tag')->get();

        return view('dashboard.content.create', compact('folders', 'tags'));
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
            'path_hi_res' => 'required|image|mimes:jpg,jpeg,png|max:10240',
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

        $folders = $request->user()->folders()->get();
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

    public function folderIndex(Request $request)
    {
        $user = $request->user();
        $folders = $user->folders()->latest()->paginate(10);
        // // Menentukan folder saat ini
        // $currentFolder = $folder;

        // // Otorisasi: Pastikan user hanya mengakses folder miliknya sendiri
        // if ($currentFolder) {
        //     $this->authorize('view', $currentFolder);
        // }

        // ambil subfolder
        // jika $currentFolder ada, ambil subfoldernya (children)
        // jika $currentFolder null, ambil folder root (id_parent = null)
        // $queryFolders = $currentFolder ? $currentFolder->children() : $user->folders()->whereNull('id_parent');

        // $folders = $queryFolders->orderBy('folder_name')->get();

        // ambil konten
        // jika $currentFolder ada, ambil konten di dalamnya
        // jika $currentFolder null, ambil konten yang tidak berada di folder manapun (id_folder = null)
        // $queryContents = $folder ? $folder->contents() : $user->contents()->whereNull('id_folder');

        // $contents = $queryContents->with('folder', 'tags')->latest()->paginate(12);

        // // Breadcrumbs
        // $breadcrumbs = collect();
        // $tempFolder = $currentFolder;
        // while ($tempFolder) {
        //     $breadcrumbs->prepend($tempFolder);
        //     $tempFolder = $tempFolder->parent; // memanggil relasi parent di Model Folder
        // };

        // Kirim data ke view
        // return view('dashboard.folder.index', compact('contents'));

        return view('dashboard.folder.index', compact('folders'));
    }

    // menampilkan form untuk menambahkan folder baru
    public function createFolder(Request $request, ?Folder $folder)
    {
        // Ambil semua folder milik pengguna yang sedang login
        $allFolders = $request->user()->folders()->orderBy('folder_name')->get();

        return view('dashboard.folder.create', compact('allFolders'));
    }

    // menyimpan folder baru
    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            // 'id_parent' => [
            //     'nullable',
            //     Rule::exists('tb_folder', 'id')->where(function ($query) {
            //         return $query->where('id_users', Auth::id());
            //     })
            // ],
        ]);

        $request->user()->folders()->create($validated);

        return redirect()->route('folder.index')->with('success', 'Folder created successfully!');
    }

    public function editFolder(Request $request, Folder $folder)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update folder ini. Ini akan memanggil FolderPolicy@update
        $this->authorize('update', $folder);

        // $folder = $request->user()->folders()->get();

        // dapatkan daftar folder yang bisa dipilih sebagai parent,
        // kecuali folder itu sendiri (untuk menghindari circular reference)
        // $possibleParents = $allFolders->where('id', '!=', $folder->id);

        return view('dashboard.folder.edit', compact('folder'));
    }

    public function updateFolder(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            // 'id_parent' => [
            //     'nullable', // Boleh 'null' (jika dipindah ke root)
            //     Rule::exists('tb_folder', 'id')->where('id_users', Auth::id()), // Harus milik user
            //     Rule::notIn([$folder->id]) // Tidak boleh sama dengan dirinya sendiri
            // ],
        ]);

        $folderData = [
            'folder_name' => $validated['folder_name'],
            'folder_description' => $validated['folder_description'],
            // 'id_parent' => $validated['id_parent'],
        ];

        // update data
        $folder->fill($folderData);
        $folder->save();

        return redirect()->route('folder.index')->with('success', 'Folder updated successfully!');
    }

    public function destroyFolder(Folder $folder)
    {
        $this->authorize('delete', $folder);

        // Simpan parent id untuk redirect setelah penghapusan
        // $parentFolder = $folder->parent;

        // INI AKAN MEMICU SEMUA KEJADIAN:
        // - DB akan menghapus folder ini
        // - DB (via cascade) akan menghapus semua sub-folder
        // - DB (via cascade) akan menghapus semua 'content' di folder ini & sub-folder
        // - Model Event 'deleting' akan dipanggil untuk 
        //   setiap 'content' yang dihapus, dan membersihkan file di storage.
        $folder->delete();

        return redirect()->route('folder.index')->with('success', 'Folder deleted successfully!');
    }
}
