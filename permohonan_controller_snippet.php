/**
* Get discussion history for a specific file.
*/
public function getFileDiskusi(string $uuid)
{
$file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

// Ensure user has access
$permohonan = $file->permohonan;
if (Auth::user()->hasRole('pemohon') && $permohonan->id_pemohon_0 != Auth::id()) {
abort(403);
}

$histori = PermohonanHistori::where('id_file', $file->id)
->with(['operator'])
->orderBy('created_at')
->get()
->map(function ($item) {
return [
'id' => $item->id,
'operator' => $item->operator,
'komentar' => $item->komentar ?? $item->deskripsi,
'created_at' => $item->created_at,
'formatted_time' => $item->created_at->format('d M Y H:i'),
'is_me' => $item->id_operator === Auth::id(),
];
});

return response()->json($histori);
}

/**
* Store a discussion message for a file.
*/
public function storeFileDiskusi(Request $request, string $uuid)
{
$file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

$request->validate(['message' => 'required|string']);

$histori = PermohonanHistori::create([
'id_permohonan' => $file->id_permohonan,
'id_operator' => Auth::id(),
'id_file' => $file->id,
'deskripsi' => 'Komentar baru.',
'komentar' => $request->message,
]);

return response()->json([
'id' => $histori->id,
'operator' => Auth::user(),
'komentar' => $histori->komentar,
'formatted_time' => $histori->created_at->format('d M Y H:i'),
'is_me' => true,
]);
}

/**
* Review a file (Approve/Reject) - Admin only.
*/
public function reviewFile(Request $request, string $uuid)
{
$file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

// Ensure user is admin/verifier
if (!Auth::user()->hasAnyRole(['admin', 'super-admin', 'verifikator'])) {
abort(403, 'Unauthorized');
}

$validated = $request->validate([
'status' => 'required|integer|in:1,2', // 1=Approved, 2=Rejected
'komentar' => 'nullable|string',
]);

$file->update([
'status' => $validated['status'],
'komentar' => $validated['komentar'] ?? null,
'reviewer_id' => Auth::id(),
'reviewed_at' => now(),
]);

$statusLabel = $validated['status'] == 1 ? 'Disetujui' : 'Ditolak';

PermohonanHistori::create([
'id_permohonan' => $file->id_permohonan,
'id_operator' => Auth::id(),
'id_file' => $file->id,
'deskripsi' => "Dokumen {$file->label} telah {$statusLabel}.",
'komentar' => $validated['komentar'] ?? null,
]);

return response()->json(['message' => 'Success', 'file' => $file]);
}

/**
* Upload a revision for a rejected file.
*/
public function uploadFileRevision(Request $request, string $uuid)
{
$file = PermohonanFile::where('uuid', $uuid)->firstOrFail();

// Ensure strictly for Rejected files
if ($file->status != PermohonanFile::STATUS_DITOLAK) {
abort(400, 'File is not in rejected status.');
}

$request->validate([
'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
]);

// Upload new file
$newFile = $request->file('file');
$filename = time() . '_REVISION_' . $file->label . '.' . $newFile->getClientOriginalExtension();
$path = $newFile->storeAs('uploads/permohonan/' . $file->id_permohonan, $filename, 'public');

// Update record
$oldPath = $file->file_path ?? str_replace('storage/', '', $file->file);

// Optional: Delete old file or archive it? For now, we overwrite the reference, but keep file on disk maybe?
// Let's just update the reference.

$file->update([
'file' => 'storage/' . $path,
'file_path' => $path,
'file_name' => $newFile->getClientOriginalName(),
'status' => PermohonanFile::STATUS_DIPROSES, // Reset to Pending
'komentar' => null, // Clear rejection comment
]);

PermohonanHistori::create([
'id_permohonan' => $file->id_permohonan,
'id_operator' => Auth::id(),
'id_file' => $file->id,
'deskripsi' => "Revisi dokumen {$file->label} diupload.",
]);

return response()->json(['message' => 'Revision uploaded', 'file' => $file]);
}