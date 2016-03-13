<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use App\Respond\Libraries\Utilities;

class PageController extends Controller
{

  /**
   * Lists the pages for given site
   *
   * @return Response
   */
  public function listAll(Request $request)
  {

    // get request data
    $userId = $request->input('userId');
    $siteId = $request->input('siteId');
    $friendlyId = $request->input('friendlyId');

    // get base path for the site
    $dir = $file = app()->basePath().'/public/sites/'.$friendlyId;

    // todo: try to read from sites/site-name/data/pages.json, then generate $arr if not available
    $json = $dir.'/data/pages.json';

    if(file_exists($json)) {

      $json = file_get_contents($json);

      // decode json file
      $arr = json_decode($json, true);

    }
    else{

      // list pages in the site
      $arr = Utilities::ListPages($dir, $userId, $friendlyId);

      // encode arr
      $content = json_encode($arr);

      Utilities::SaveContent($dir.'/data/', 'pages.json', $content);

    }

    return response()->json($arr);

  }

  /**
   * Lists the routes for given site
   *
   * @return Response
   */
  public function listRoutes(Request $request)
  {

    // get request data
    $userId = $request->input('userId');
    $siteId = $request->input('siteId');
    $friendlyId = $request->input('friendlyId');

    // get base path for the site
    $dir = $file = app()->basePath().'/public/sites/'.$friendlyId;

    $arr = array_merge(array('/'), Utilities::ListRoutes($dir, $friendlyId));

    return response()->json($arr);

  }

  /**
   * Saves the page and the fragment
   *
   * @return Response
   */
  public function save(Request $request)
  {
    // get request data
    $userId = $request->input('userId');
    $siteId = $request->input('siteId');
    $friendlyId = $request->input('friendlyId');

    // get url & changes
    $url = $request->json()->get('url');
    $changes = $request->json()->get('changes');

    // page file
    $path = rtrim(app()->basePath('public/sites/'.$url), '/');

    $fragment = str_replace($friendlyId.'/', '', $url);
    $fragment = str_replace('/', '.', $fragment);

    // full path
    $fragment = rtrim(app()->basePath('public/sites/'.$friendlyId.'/templates/page/'.$fragment), '/');

    if(file_exists($path)) {

      $html = file_get_contents($path);

      // open document
      $doc = \phpQuery::newDocument($html);

      // walk through changes
      foreach($changes as $change){

        $selector = $change['selector'];
        $html = $change['html'];

        // set new HTML
        $doc[$selector] = $html;

        if($selector == '#content') {
          file_put_contents($fragment, $html);
        }

      }

      // save file
      file_put_contents($path, $doc->htmlOuter());

      // return OK
      return response('OK', 200);

    }

    // return a bad request
    return response('Path not found', 400);

  }

  /**
   * Adds the page and the fragment
   *
   * @return Response
   */
  public function add(Request $request)
  {
    // get request data
    $userId = $request->input('userId');
    $siteId = $request->input('siteId');
    $friendlyId = $request->input('friendlyId');

    // get url, title and description
    $url = $request->json()->get('url');
    $title = $request->json()->get('title');
    $description = $request->json()->get('description');

    return response('OK', 200);

    /*

    // page file
    $path = rtrim(app()->basePath('public/sites/'.$url), '/');

    // fragment file
    $fragment = str_replace('/', '.', $fragment);

    // full path
    $fragment = rtrim(app()->basePath('public/sites/'.$friendlyId.'/templates/page/'.$fragment), '/');

    if(file_exists($path)) {

      if($selector == '#content') {
        file_put_contents($fragment, $html);
      }

      // save file
      file_put_contents($path, $html);

      // return OK
      return response('OK', 200);

    }

    */

    // return a bad request
    return response('Path not found', 400);

  }

}