<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(Slider $data)
    {
        $this->title = 'Slider';
        $this->view = 'Backend/Master/Slider';
        $this->prefix = 'master.slider';
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
            new Middleware('can:master.slider.index', only: ['index']),
            new Middleware('can:master.slider.create', only: ['create', 'store']),
            new Middleware('can:master.slider.edit', only: ['edit', 'update']),
            new Middleware('can:master.slider.destroy', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = $this->data::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('label', 'like', "%{$search}%");
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
            'label' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sliders', 'public');
        }

        $this->data::create([
            'label' => $validated['label'],
            'file' => $imagePath,
            'kategori' => 'slider',
            'id_operator' => auth()->id() ?? 0,
        ]);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Slider berhasil ditambahkan.');
    }

    public function edit($uuid)
    {
        $slider = $this->data::uuid($uuid)->firstOrFail();

        return Inertia::render("$this->view/Edit", [
            'slider' => $slider,
            'share' => $this->share,
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $slider = $this->data::uuid($uuid)->firstOrFail();

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dataToUpdate = [
            'label' => $validated['label'],
        ];

        if ($request->hasFile('image')) {
            if ($slider->file) {
                Storage::disk('public')->delete($slider->file);
            }
            $dataToUpdate['file'] = $request->file('image')->store('sliders', 'public');
        }

        $slider->update($dataToUpdate);

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Slider berhasil diperbarui.');
    }

    public function destroy($uuid)
    {
        $slider = $this->data::uuid($uuid)->firstOrFail();

        if ($slider->file) {
            Storage::disk('public')->delete($slider->file);
        }

        $slider->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Slider berhasil dihapus.');
    }
}
