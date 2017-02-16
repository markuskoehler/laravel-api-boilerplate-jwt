<?php
namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegacyController {
    function getDuration($start) {
        $parsedObj = date_parse($start);

        if($start !== null && isset($parsedObj['error_count']) && $parsedObj['error_count'] != 0) {
            return Response(['error' => true, 'message' => 'Specified date is not valid.']);
        }

        $d1 = new \DateTime(($start !== null) ? $start : "2013-10-09");
        $d2 = new \DateTime(); // today

        return Response([
            'years' => $d1->diff($d2)->y,
            'months' => $d1->diff($d2)->m,
            'days' => $d1->diff($d2)->d
        ]);
    }

    function getOwnRecipes() {
        $recipes = DB::table('recipes_categories_mapping')
            ->join('recipes_categories', 'recipes_categories_mapping.categories_id', '=', 'recipes_categories.id')
            ->join('recipes', 'recipes_categories_mapping.recipes_id', '=', 'recipes.id')
            ->select('recipes.id', 'recipes.name', 'image', 'comment', 'recipes_categories.name AS category')
            ->get();

        return Response(
            $recipes
        );
    }

    function getCkRecipes($suchbegriff) {
        $recipes = file_get_contents('http://api.chefkoch.de/api/1.2/api-recipe-search.php?Suchbegriff=' .
            urlencode($suchbegriff) . '&i=0&z=1&m=0&o=0&t=&limit=20');
        return Response($recipes);
    }

    function getCkRecipeDetails($showId) {
        $recipeDetails = file_get_contents('http://api.chefkoch.de/api/1.2/api-recipe.php?ID=' . $showId . '&divisor=0&limit=1');
        return Response($recipeDetails);
    }

    // coordinates are Altleiningen, Germany
    function getSunrise() {
        $data = file_get_contents('http://api.sunrise-sunset.org/json?lat=49.5061335&lng=8.07517&date=today&formatted=0');
        $decdata = json_decode($data);
        $sunrise = ['sunrise' => $decdata->results->sunrise];
        return Response($sunrise);
    }

    function getSunset() {
        $data = file_get_contents('http://api.sunrise-sunset.org/json?lat=49.5061335&lng=8.07517&date=today&formatted=0');
        $decdata = json_decode($data);
        $sunset = ['sunset' => $decdata->results->sunset];
        return Response($sunset);
    }

    function getUmsaetze() {
        $res = DB::connection('hibiscus')->table('umsatz')->get();
        return Response($res);
    }

    function anyStorage(Request $request) {
        //$method = $request->method();

        if ($request->isMethod('get')) {
            $res = DB::connection('homecm')->table('storage')->select(DB::raw('hex(pkid), basepath'))->get();
            return Response($res);
        } else if($request->isMethod('post')) { // insert
            DB::connection('homecm')->table('storage')->insert(
                ['pkid' => DB::raw("UNHEX(REPLACE(UUID(),'-',''))"), 'basepath' => $request->input('basepath'), 'added_date' => Carbon::now()]
            );
        } else if($request->isMethod('put')) { // update

            //} else if($request->isMethod('patch')) {

        } else if($request->isMethod('delete')) {

        } else {
            // not implemented
            abort(501, 'Not implemented');
        }
    }

    function anyFiletype(Request $request) {
        if ($request->isMethod('get')) {
            $res = DB::connection('homecm')->table('filetype')->select(DB::raw('hex(pkid), extension'))->get();
            return Response($res);
        } else if($request->isMethod('post')) {
            DB::connection('homecm')->table('filetype')->insert(
                ['pkid' => DB::raw("UNHEX(REPLACE(UUID(),'-',''))"), 'extension' => $request->input('extension'), 'added_date' => Carbon::now()]
            );
        } else if($request->isMethod('put')) {

            //} else if($request->isMethod('patch')) {

        } else if($request->isMethod('delete')) {

        } else {
            // not implemented
            abort(501, 'Not implemented');
        }
    }

    function anySoftware(Request $request) {
        if ($request->isMethod('get')) {
            //$res = DB::connection('homecm')->table('software')->select(DB::raw('hex(pkid), hex(storage_id), directory, filename, hex(filetype_id), description, version'))->get();
            $res = DB::connection('homecm')->table('v_software')->get();
            return Response($res);
        } else if($request->isMethod('post')) {
            $path = $request->input('basepath');
            $ext = $request->input('extension');

            $pathID = DB::connection('homecm')->table('storage')->select(DB::raw('hex(pkid) as pkid'))->where('basepath', '=', $path)->first()->pkid;
            $extID = DB::connection('homecm')->table('filetype')->select(DB::raw('hex(pkid) as pkid'))->where('extension', '=', $ext)->first()->pkid;

            DB::connection('homecm')->table('software')->insert(
                ['pkid' => DB::raw("UNHEX(REPLACE(UUID(),'-',''))"), 'storage_id' => DB::raw('unhex("' . $pathID . '")'), 'directory' => $request->input('directory'), 'filename' => $request->input('filename'),
                    'filetype_id' => DB::raw('unhex("' . $extID . '")'), 'description' => $request->input('description'), 'version' => $request->input('version'), 'added_date' => Carbon::now()]
            );
        } else if($request->isMethod('put')) {

            //} else if($request->isMethod('patch')) {

        } else if($request->isMethod('delete')) {

        } else {
            // not implemented
            abort(501, 'Not implemented');
        }
    }
}