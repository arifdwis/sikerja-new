<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FaqController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Faq $data)
    {
        $this->title = 'FAQ';
        $this->view = 'Backend/Master/Faq';
        $this->prefix = 'master.faq';
        $this->data = $data;

        $this->share = [
            'title' => $this->title,
            'view' => $this->view,
            'prefix' => $this->prefix
        ];
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:master.faq.index', only: ['index']),
            new Middleware('can:master.faq.create', only: ['create', 'store']),
            new Middleware('can:master.faq.edit', only: ['edit', 'update']),
            new Middleware('can:master.faq.destroy', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = $this->data::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('label', 'like', "%{$search}%")
                ->orWhere('jawaban', 'like', "%{$search}%");
        }

        $datas = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render("$this->view/Create", [
            'share' => $this->share,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'nullable|string',
        ]);

        $this->data::create([
            'label' => $validated['question'],
            'jawaban' => $validated['answer'],
        ]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $faq = $this->data::uuid($uuid)->firstOrFail();

        return Inertia::render("$this->view/Edit", [
            'faq' => $faq,
            'share' => $this->share,
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $faq = $this->data::uuid($uuid)->firstOrFail();

        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq->update([
            'label' => $validated['question'],
            'jawaban' => $validated['answer'],
        ]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $faq = $this->data::uuid($uuid)->firstOrFail();
        $faq->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'FAQ berhasil dihapus.');
    }
}
