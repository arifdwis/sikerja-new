<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TemplateController extends Controller
{
    /**
     * Mapping kode template → file name + label.
     * File ditaruh di storage/app/templates/
     */
    protected array $templates = [
        'surat_permohonan' => [
            'file' => 'surat_permohonan_kerjasama.pdf',
            'label' => 'Template Surat Permohonan Kerjasama.pdf',
            'mime' => 'application/pdf',
        ],
        'kak' => [
            'file' => 'kak.pdf',
            'label' => 'Template KAK.pdf',
            'mime' => 'application/pdf',
        ],
        'mou' => [
            'file' => 'mou.docx',
            'label' => 'Template MOU.docx',
            'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    ];

    public function download(string $type): BinaryFileResponse
    {
        if (!isset($this->templates[$type])) {
            abort(404, 'Template tidak ditemukan.');
        }

        $tpl = $this->templates[$type];
        $path = storage_path('app/templates/' . $tpl['file']);

        if (!file_exists($path)) {
            abort(404, "File template '{$tpl['file']}' belum tersedia. Silakan hubungi admin.");
        }

        return response()->download($path, $tpl['label'], [
            'Content-Type' => $tpl['mime'],
        ]);
    }

    /**
     * List template tersedia (JSON) — untuk dipanggil di halaman upload dokumen.
     */
    public function list()
    {
        $list = [];
        foreach ($this->templates as $key => $tpl) {
            $exists = file_exists(storage_path('app/templates/' . $tpl['file']));
            $list[] = [
                'key' => $key,
                'label' => $tpl['label'],
                'available' => $exists,
                'url' => $exists ? route('template.download', $key) : null,
            ];
        }
        return response()->json($list);
    }
}
