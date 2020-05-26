<?php

namespace Moonlight\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Models\UserActionType;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Throwable;

class LogController extends Controller
{
    /**
     * User actions per page.
     */
    const PER_PAGE = 10;

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function next(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return response()->json(['error' => 'error_admin_access_denied']);
        }

        $html = $this->search($request);

        return response()->json(['html' => $html]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return redirect()->route('home');
        }

        $users = User::orderBy('login', 'asc')->get();

        $userActionTypes = UserActionType::getActionTypeNameList();

        $action = $request->input('action');
        $userId = $request->input('user');
        $typeId = $request->input('type');
        $comments = $request->input('comments');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');

        $html = $action == 'search' ? $this->search($request) : null;

        return view('moonlight::log.index', [
            'users' => $users,
            'userActionTypes' => $userActionTypes,
            'action' => $action,
            'userId' => $userId,
            'typeId' => $typeId,
            'comments' => $comments,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'html' => $html,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|string
     */
    protected function search(Request $request)
    {
        $user = $request->input('user');
        $type = $request->input('type');
        $comments = $request->input('comments');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');

        if ($type && ! UserActionType::actionTypeExists($type)) {
            $type = null;
        }

        $dateFrom = $dateFrom ? Carbon::createFromFormat('Y-m-d', $dateFrom) : null;
        $dateTo = $dateTo ? Carbon::createFromFormat('Y-m-d', $dateTo) : null;

        $userActions = UserAction::where(function ($query) use ($user, $type, $comments, $dateFrom, $dateTo) {
            if ($user) {
                $query->where('user_id', $user);
            }

            if ($type) {
                $query->where('action_type_id', $type);
            }

            if ($comments) {
                $query->where('comments', 'ilike', "%$comments%");
            }

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom->format('Y-m-d'));
            }

            if ($dateTo) {
                $query->where('created_at', '<', $dateTo->format('Y-m-d'));
            }
        })
            ->orderBy('created_at', 'desc')
            ->paginate(static::PER_PAGE);

        try {
            return view('moonlight::log.list', [
                'total' => $userActions->total(),
                'currentPage' => $userActions->currentPage(),
                'hasMorePages' => $userActions->hasMorePages(),
                'userActions' => $userActions,
            ])->render();
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
