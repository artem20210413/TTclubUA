<?php

namespace App\Http\Controllers;

class WellKnownController extends Controller
{
    public function appleAppSiteAssociation()
    {
        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => [
                    [
                        'appID' => config('mobile.ios.team_id').'.'.config('mobile.ios.bundle_id'),
                        'paths' => ['/auth/tg-code*'],
                    ],
                ],
            ],
        ]);
    }

    public function assetlinks()
    {
        return response()->json([
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => config('mobile.android.package_name'),
                    'sha256_cert_fingerprints' => config('mobile.android.sha256_cert_fingerprints'),
                ],
            ],
        ]);
    }
}
