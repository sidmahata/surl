<?php

namespace Acme\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApigraphController extends Controller
{
    public function indexAction()
    {

        // this token is used to authenticate your API request.
// You can get the token on the API page inside your Piwik interface
        $token_auth = '90871c8584ddf2265f54553a305b6ae1';

// we call the REST API and request the 100 first keywords for the last month for the idsite=7
        $url = "https://localhost/piwik/";
        $url .= "?module=API&method=Actions.getPageUrl";
        $url .= "&pageUrl=http://localhost/surl/web/template1";
        $url .= "&period=day&date=last30&idSite=1";
        $url .= "&format=xml";
        $url .= "&token_auth=$token_auth";

        $data = file_get_contents($url);
        $fetched = simplexml_load_string($data);

//        $readxml = $fetched->result[1]['date'];

        $nb_hits_array_final = array();

        foreach($fetched->result as $datedata) {

            $nb_hits_array_row = ["date" => (string)$datedata['date'], "nb_hits" => (int)$datedata->row->nb_hits, "nb_visits" => (int)$datedata->row->nb_visits];
//            $nb_hits_array_final = array_push($nb_hits_array, $nb_hits_array_row);
            array_push($nb_hits_array_final, $nb_hits_array_row);
        }

        $jsonfetched = json_encode($nb_hits_array_final);



// case error
//        if (!$content) {
//            print("Error, content fetched = " . $fetched);
//        }


//        print("<h1>Keywords for the last month</h1>\n");
//        foreach ($content as $row) {
//            $keyword = htmlspecialchars(html_entity_decode(urldecode($row['label']), ENT_QUOTES), ENT_QUOTES);
//            $hits = $row['nb_visits'];
//
//            print("<b>$keyword</b> ($hits hits)<br>\n");
//        }
//
        return $this->render('AcmeTestBundle:Apigraph:index.html.twig', array(
            'apifetched' => $jsonfetched
        ));
//        return new response($fetched);

    }
}
