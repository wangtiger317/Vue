<?php

namespace Platform\Controllers\Staff;

use App\Customer;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Staff;
use App\Http\Controllers;
use Platform\Controllers\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller {

    /*
     |--------------------------------------------------------------------------
     | History related functions
     |--------------------------------------------------------------------------
     */

    /**
     * Get staff member history.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getHistory(Request $request) {

      $campaign = \Platform\Models\Campaign::withoutGlobalScopes()->whereUuid(request('uuid', 0))->firstOrFail();
      if($request->userUuid){

          $user = Customer::withoutGlobalScopes()->whereUuid($request->userUuid)->firstOrFail();
          $locale = request('locale', config('system.default_language'));
          return response()->json($user->getHistory($locale));
      }else{
          $user = Staff::withoutGlobalScopes()->where('id', Auth::user('staff')->id)->firstOrFail();
          return response()->json($user->getHistory($campaign->id));
      }

    }
}
