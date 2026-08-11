<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\License;
use Illuminate\Validation\Rule;

class FolderController extends Controller
{
    public function index(Request $request, Folder $folder)
    {
        $user = $request->user();

        // $this->authorize('view', $folder);

        $folders = $user->folders()->whereNull('parent_id')->latest()->paginate(12);

        return view('dashboard.folder.index', compact('folders'));
    }

    // menampilkan form untuk menambahkan folder baru
    public function create(Request $request, ?Folder $folder)
    {
        // Ambil semua folder milik pengguna yang sedang login
        $allFolders = $request->user()->folders()->orderBy('folder_name')->get();

        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.folder.create', compact('allFolders', 'licenses'));
    }

    // menyimpan folder root baru
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'visibility' => 'required|in:public,private,by_request',
            'parent_id' => ['nullable', 'exists:folders,id'],
            'license_id' => [
                'nullable',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'allow_individual_sale' => 'required|boolean',
            'is_bundle' => 'required|boolean',
            'bundle_price' => 'nullable|numeric|min:0',
            // 'status' => 'required|in:active,deleted,banned',
        ]);

        $requestedIsBundle = (int) $validated['is_bundle'] === 1;

        if ($requestedIsBundle && blank($validated['license_id'])) {
            return back()
                ->withErrors([
                    'license_id' => 'License is required when folder is marked as bundle.',
                ])
                ->withInput();
        }

        if ($requestedIsBundle && blank($validated['bundle_price'])) {
            return back()
                ->withErrors([
                    'bundle_price' => 'Bundle price is required when folder is marked as bundle.',
                ])
                ->withInput();
        }

        $validated['license_id'] = $requestedIsBundle
            ? $validated['license_id']
            : null;

        $validated['bundle_price'] = $requestedIsBundle
            ? $validated['bundle_price']
            : null;

        $validated['allow_individual_sale'] = $requestedIsBundle
            ? $validated['allow_individual_sale']
            : true;

        $request->user()->folders()->create($validated);

        return redirect()->route('folder.index')->with('success', 'Folder created successfully!');
    }

    public function edit(Request $request, Folder $folder)
    {
        // OTORISASI: Cek apakah user ini boleh meng-update folder ini. Ini akan memanggil FolderPolicy@update
        $this->authorize('update', $folder);

        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.folder.edit', compact('folder', 'licenses'));
    }

    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'visibility' => 'required|in:public,private,by_request',
            'license_id' => [
                'nullable',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'allow_individual_sale' => 'nullable|boolean',
            'is_bundle' => 'required|boolean',
            'bundle_price' => 'nullable|numeric|min:0'
        ]);

        $requestedIsBundle = (int) $validated['is_bundle'] === 1;

        if ($requestedIsBundle && blank($validated['license_id'])) {
            return back()
                ->withErrors([
                    'license_id' => 'License is required when folder is marked as bundle.',
                ])
                ->withInput();
        }

        if ($requestedIsBundle && blank($validated['bundle_price'])) {
            return back()
                ->withErrors([
                    'bundle_price' => 'Bundle price is required when folder is marked as bundle.',
                ])
                ->withInput();
        }

        if ($requestedIsBundle && $folder->hasDirectSingleSaleContent()) {
            return back()
                ->withErrors([
                    'is_bundle' => 'This folder cannot be changed into a bundle because it contains single-sale content.',
                ])
                ->withInput();
        }

        $folderData = [
            'folder_name' => $validated['folder_name'],
            'folder_description' => $validated['folder_description'],
            'visibility' => $validated['visibility'],
            'license_id' => $requestedIsBundle ? $validated['license_id'] : null,
            'allow_individual_sale' => $requestedIsBundle
                ? $validated['allow_individual_sale']
                : true,
            'is_bundle' => $requestedIsBundle,
            'bundle_price' => $requestedIsBundle ? $validated['bundle_price'] : null
        ];

        // update data
        $folder->update($folderData);
        $folder->refresh();

        if ($requestedIsBundle) {
            $folder->syncDirectContentLicenses();
        }

        return redirect()->route('folder.index')->with('success', 'Folder updated successfully!');
    }

    public function destroy(Folder $folder)
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
        // Jika di root (null), ambil folder utama (yang 'parent_id'-nya null)
        // Jika di dalam folder, ambil 'children' (anak) dari folder itu
        if ($currentFolder) {
            // di dalam folder ➜ ambil anak-anaknya
            $folders = $currentFolder->children()
                ->orderBy('folder_name')
                ->get();
        } else {
            // root ➜ ambil folder milik user yang parent_id null
            $folders = $user->folders()
                ->whereNull('parent_id')
                ->orderBy('folder_name')
                ->get();
        };

        // $folders = $queryFolders->orderBy('folder_name')->get();

        //ambil konten
        // Jika di root, ambil konten yang 'folder_id'-nya null
        // Jika di dalam folder, ambil 'contents' dari folder itu
        if ($currentFolder) {
            $queryContents = $currentFolder->contents();
        } else {
            $queryContents = $user->contents();
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
        $parentId = $request->query('parent_id');

        $parentFolder = null;

        if ($parentId) {
            $parentFolder = Folder::where('id', $parentId)
                ->where('seller_id', $user->id)
                ->firstOrFail();
        }

        $licenses = License::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.folder.detail-folder-create', compact('parentFolder', 'licenses'));
    }

    public function storeDetailFolder(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'folder_name' => 'required|string|max:255',
            'folder_description' => 'nullable|string|max:1000',
            'visibility' => 'required|in:public,private,by_request',
            'parent_id' => ['required', 'exists:folders,id'],
            'license_id' => [
                'nullable',
                Rule::exists('licenses', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'allow_individual_sale' => 'required|boolean',
            'is_bundle' => 'required|boolean',
            'bundle_price' => 'nullable|numeric|min:0',
        ]);

        $requestedIsBundle = (int) $validated['is_bundle'] === 1;

        if ($requestedIsBundle && blank($validated['license_id'])) {
            return back()
                ->withErrors([
                    'license_id' => 'License is required when folder is marked as bundle.',
                ])
                ->withInput();
        }

        if ($requestedIsBundle && blank($validated['bundle_price'])) {
            return back()
                ->withErrors([
                    'bundle_price' => 'Bundle price is required when folder is marked as bundle.',
                ])
                ->withInput();
        }

        $validated['license_id'] = $requestedIsBundle
            ? $validated['license_id']
            : null;

        $validated['bundle_price'] = $requestedIsBundle
            ? $validated['bundle_price']
            : null;

        // $parentFolder = Folder::where('id', $validated['parent_id'])
        //     ->where('seller_id', $user->id)
        //     ->firstOrFail();

        // if ($parentFolder->is_bundle == 1 && blank($validated['bundle_price'])) {
        //     return back()
        //         ->withErrors(['bundle_price' => 'Bundle price must be provided when the folder is marked as a bundle.'])
        //         ->withInput();
        // }

        // $bundlePrice = $validated['is_bundle'] == 1 ? $validated['bundle_price'] : null;
        // $validated['bundle_price'] = $bundlePrice;

        $user->folders()->create($validated);

        // Kalau ada parent, balik ke folder tersebut
        if (!empty($validated['parent_id'])) {
            return redirect()
                ->route('detail.folder.show', $validated['parent_id'])
                ->with('success', 'Subfolder berhasil dibuat.');
        }

        // Kalau tidak ada parent → folder di root
        return redirect()
            ->route('folder.index')
            ->with('success', 'Folder root berhasil dibuat.');
    }

    public function folderMove(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $user = $request->user();

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                Rule::exists('folders', 'id')->where(function ($query) use ($user) {
                    // pastikan folder tujuan milik user yang sama
                    return $query->where('seller_id', $user->id);
                }),
            ],
        ]);

        $destinationFolderId = $validated['parent_id'] ?? null;

        // Cegah folder jadi parent dirinya sendiri
        if ($destinationFolderId && $destinationFolderId == $folder->id) {
            return back()->withErrors([
                'parent_id' => 'Folder tidak boleh menjadi parent dirinya sendiri'
            ]);
        }

        if ($destinationFolderId) {
            $parentFolder = Folder::where('id', $destinationFolderId)
                ->where('seller_id', $user->id)
                ->firstOrFail();

            $folder->parent_id = $parentFolder->id;
            $folder->save();

            // samakan visibilitas folder, subfolder, dan content
            $folder->updateVisibilityRecursive($parentFolder->visibility);
        } else {
            $folder->parent_id = null;
            $folder->save();
        }

        // $folder->parent_id = $destinationFolderId;

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
