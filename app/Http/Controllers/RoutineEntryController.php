<?php
namespace App\Http\Controllers;

use App\Models\Routine;
use Illuminate\Http\Request;

class RoutineEntryController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function select(Request $request, int $id)
    {
        $routine = Routine::findOrFail($id);
        $mode = $request->query('mode');
        if ($mode === 'exam') return app(RoutineItemCtrl::class)->addItem($id);
        if (in_array($mode, ['revision', 'model'], true)) {
            return app(RoutineDerivedExamController::class)->create($request, $id, $mode);
        }
        $selectedMode = null;
        return view('layouts.routines.select-item-type', compact('routine', 'selectedMode'));
    }
}
