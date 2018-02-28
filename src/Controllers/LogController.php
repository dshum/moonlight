<?php

namespace Moonlight\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Moonlight\Main\UserActionType;
use Moonlight\Models\Group;
use Moonlight\Models\User;
use Moonlight\Models\UserAction;
use Carbon\Carbon;

class LogController extends Controller
{
    public function next(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();

        if (! $loggedUser->hasAccess('admin')) {
            return response()->json(['error' => 'error_admin_access_denied']);
        }
        
        $html = $this->search($request);
        
        return response()->json(['html' => $html]);
    }
    
    public function index(Request $request)
    {
        $scope = [];
        
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

        if ($action == 'search') {
            $html = $this->search($request);
        } else {
            $html = null;
        }
        
        $scope['users'] = $users;
        $scope['userActionTypes'] = $userActionTypes;
        $scope['action'] = $action;
        $scope['userId'] = $userId;
        $scope['typeId'] = $typeId;
        $scope['comments'] = $comments;
        $scope['dateFrom'] = $dateFrom;
        $scope['dateTo'] = $dateTo;
        $scope['html'] = $html;
        
        return view('moonlight::log', $scope);
    }

    protected function search(Request $request)
    {
        $scope = [];
        
        $loggedUser = Auth::guard('moonlight')->user();
        
        $user = $request->input('user');
        $type = $request->input('type');
        $comments = $request->input('comments');
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        
        if ($type && ! UserActionType::actionTypeExists($type)) {
			$type = null;
		}

		if ($dateFrom) {
			try {
				$dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom);
			} catch (\Exception $e) {
				$dateFrom = null;
			}
		}

		if ($dateTo) {
			try {
				$dateTo = Carbon::createFromFormat('Y-m-d', $dateTo)->modify('+1 day');
			} catch (\Exception $e) {
				$dateTo = null;
			}
		}

        $criteria = UserAction::where(
			function($query) use (
				$user, $type, $comments, $dateFrom, $dateTo
			) {
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
			}
		);

		$criteria->orderBy('created_at', 'desc');

		$userActions = $criteria->paginate(10);
        
        $total = $userActions->total();
		$currentPage = $userActions->currentPage();
        $hasMorePages = $userActions->hasMorePages();

        $scope['total'] = $total;
        $scope['currentPage'] = $currentPage;
        $scope['hasMorePages'] = $hasMorePages;
        $scope['userActions'] = $userActions;

        $html = view('moonlight::logList', $scope)->render();
        
        return $html;
    }
}