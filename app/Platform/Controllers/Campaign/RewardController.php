<?php

namespace Platform\Controllers\Campaign;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Staff;
use App\Customer;
use Platform\Models\Code;
use Platform\Models\History;
use App\Http\Controllers;
use Platform\Controllers\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barzo\Password\Generator;

class RewardController extends Controller {

    /*
     |--------------------------------------------------------------------------
     | Reward related functions
     |--------------------------------------------------------------------------
     */

    /**
     * Get code used to redeem a rewards with a link (e.g. QR code).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postGetRedeemRewardToken(Request $request) {
      $account = app()->make('account');
      $campaign = \Platform\Models\Campaign::withoutGlobalScopes()->whereUuid(request('campaign', 0))->firstOrFail();
      $reward = \Platform\Models\Reward::whereUuid(request('reward', 0))->firstOrFail();

      // First, delete existing codes

//        $last_token = DB::table('codes')->where([ ['type', 'reward_token'], ['customer_id', '=', auth('customer')->user()->id]])->select('code')->orderBy('id','asc')->get();

      $last_token = Code::where('type', 'reward_token')->where('customer_id', auth('customer')->user()->id)->select('code')->orderBy('id','asc')->first();

      if($last_token!= null){
          $x = $last_token->code;
      }else{
          $x = '';
      }


      $deletedRows = Code::where('type', 'reward_token')->where('customer_id', auth('customer')->user()->id)->delete();

      // Add generated code to codes table, expire in 1 hour
      $token = $this->getUniqueCode('reward_token', $campaign->id);

      $expires_at = Carbon::now()->addHours(1);

      $code = new Code;
      $code->campaign_id = $campaign->id;
      $code->customer_id = auth('customer')->user()->id;
      $code->reward_id = $reward->id;

      $code->type = 'reward_token';
      if($x!=null){
          $code->code = $x;
      }else{
          $code->code = $token;
      }

      $code->expires_at = $expires_at;
      $code->created_by = $campaign->created_by;

      $code->save();

      return response()->json([
        'status' => 'success',
        'token' => $code->code
      ], 200);
    }

    /**
     * Make sure code is unique
     *
     * @return boolean
     */
    public function getUniqueCode($type, $campaign_id) {
      if ($type == 'reward_token') {
        $token = Str::random(8);
      }

      $code = Code::where('campaign_id', $campaign_id)->where('type', $type)->where('code', $token)->first();

      if ($code === null) {
        return $token;
      } else {
        return $this->getUniqueCode($type, $campaign_id);
      }
    }

    /**
     * Merchant verifies generated code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postVerifyMerchantCode(Request $request) {
      $account = app()->make('account');
      $campaign = \Platform\Models\Campaign::withoutGlobalScopes()->whereUuid(request('campaign', 0))->firstOrFail();
      $code = $request->code;

      // Set language locale
      $locale = request('locale', config('system.default_language'));
      app()->setLocale($locale);

      // Find code
      $code = Code::where('code', $code)->where('campaign_id', $campaign->id)->where('type', 'merchant')->where('expires_at', '>', \Carbon\Carbon::now())->first();

      if ($code === null) {
        return response()->json([
          'status' => 'error',
          'errors' => ['code' => trans('campaign.code_invalid_or_expired')]
        ], 422);
      }

      // Code is correct, let merchant choose reward and segments
      $rewards = $campaign->getAciveRewards()->pluck('title_with_points', 'uuid');
      $segments = $campaign->business->segments->pluck('name', 'id');

      return response()->json([
        'status' => 'success',
        'segments' => $segments,
        'rewards' => $rewards
      ], 200);
    }

    /**
     * Initially merhant code was correct, double check code and process reward and segments
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postProcessMerchantEntry(Request $request) {
      $account = app()->make('account');
      $campaign = \Platform\Models\Campaign::withoutGlobalScopes()->whereUuid(request('campaign', 0))->firstOrFail();
      $code = $request->code;
      $reward = $request->reward;
      $reward = \Platform\Models\Reward::whereUuid(request('reward', 0))->firstOrFail();
      $segments = $request->segments;

      // Set language locale
      $locale = request('locale', config('system.default_language'));
      app()->setLocale($locale);

      // Find code
      $code = Code::where('code', $code)->where('campaign_id', $campaign->id)->where('type', 'merchant')->where('expires_at', '>', \Carbon\Carbon::now())->first();

      if ($code === null) {
        return response()->json([
          'status' => 'error',
          'errors' => ['code' => trans('campaign.code_invalid_or_expired')]
        ], 422);
      }

      // Verify if customer has enough points
      $new_points_customer = auth('customer')->user()->points - $reward->points_cost;

      if ($new_points_customer < 0) {
        return response()->json([
          'status' => 'error',
          'errors' => ['reward' => 'Este cliente n??o tem pontos suficientes para este Pr??mio']
        ], 422);
      }

      // Code is correct, process points and segments
      $history = new History;
      $history->customer_id = auth('customer')->user()->id;
      $history->campaign_id = $campaign->id;
      $history->staff_id = $code->staff_id;
      $history->staff_name = $code->staff_name;
      $history->staff_email = $code->staff_email;
      $history->reward_id = $reward->id;
      $history->reward_title = $reward->title;
      $history->points = -$reward->points_cost;
      $history->event = 'Atribu??do por c??digo';
      $history->created_by = $campaign->created_by;

      $history->save();

      // Segments
      if (is_array($segments) && count($segments) > 0) {
        $history->segments()->sync($segments);
      }

      // Increment reward redemptions
      $reward->increment('number_of_times_redeemed');

      return response()->json([
        'status' => 'success',
        'reward' => $reward->title_with_points,
        'points' => $new_points_customer
      ], 200);
    }
}
