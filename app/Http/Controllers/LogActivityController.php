<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\UserActivity;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LogActivityController extends Controller implements HasMiddleware
{
    protected $title;
    protected $prefix;
    protected $view;
    protected $data;
    protected $share;

    public function __construct(UserActivity $data)
    {
        $this->title = 'Log Activity';
        $this->view = 'Backend/Settings/LogActivity';
        $this->prefix = 'settings.log-activity';
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
            new Middleware('can:settings.log-activity.index', only: ['index']),
            new Middleware('can:settings.log-activity.show', only: ['show']),
            new Middleware('can:settings.log-activity.destroy', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->data::with('causer')->latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('method', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%");
            });
        }

        if ($request->has('log_name') && $request->log_name) {
            $query->where('log_name', $request->log_name);
        }

        $datas = $query->paginate(20)->withQueryString();

        $logNames = $this->data::select('log_name')->distinct()->pluck('log_name');

        return Inertia::render("$this->view/Index", [
            'datas' => $datas,
            'share' => $this->share,
            'logNames' => $logNames,
            'filters' => $request->only(['search', 'log_name']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $activity = $this->data::with('causer')->findOrFail($id);

        return Inertia::render("$this->view/Show", [
            'activity' => $activity,
            'share' => $this->share,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $activity = $this->data::findOrFail($id);
        $activity->delete();

        return redirect()->route("$this->prefix.index")
            ->with('success', 'Log activity deleted successfully.');
    }
}
