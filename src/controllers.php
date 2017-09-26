<?php

namespace Brewerydb;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Silex\CsrfTokenServiceProvider;
use Brewerydb\Pintlabs;


class ControllerProvider implements ControllerProviderInterface
{

    private $app;

    public function connect(Application $app)
    {
        $app->error([$this, 'error']);

        $controllers = $app['controllers_factory'];

        $controllers
            ->get('/', [$this, 'homepage'])
            ->bind('homepage');

        $controllers
            ->post('/search', [$this, 'search'])
            ->bind('search');




        return $controllers;
    }


    public function homepage(Application $app)
    {

        // Generate CSRF Token
        $csrf_token = $app['csrf.token_manager']->getToken('token_id');

        // Get random beer
        $random_beer_data = $this->requireRandomBeer();

        return $app['twig']->render('index.html.twig', [
            'csrf_token'  => $csrf_token,
            'brewery_id'  => $random_beer_data['brewery_id'],
            'beer_name'   => $random_beer_data['beer_name'],
            'beer_img'    => $random_beer_data['beer_img'],
            'beer_description' => $random_beer_data['description']
        ]);

    }


    /**
     * @return array
     */

    private function requireRandomBeer()
    {
        // Prepare $params requested in BreweryDb API
        $param =  $this->setParameter($page = 1, $randomBrewery = true, $searchBeersByBreweryId = null);

        // Try get a random beer until have name, image and description
        do {

            if ( isset($results) ) { unset($results); }

            // Request Brewery API
            $results = $this->requestBreweryDbAPI($search_type = 'beers', $search_text = '', $param);

            // Get 20 random beer and pick up the first record that has all the fields name, description and image

            foreach ($results['data'] as $result)
            {

                $brewery_id  = $result['breweries'][0]['id'];
                $beer_name   = (isset($result['name']))            ? $result['name']           : '';
                $beer_img    = (isset($result['labels']['icon'] )) ? $result['labels']['icon'] : '';
                $description = (isset($result['description']))     ? $result['description']    : '';

                if ( (!empty($beer_name))   and
                     (!empty($beer_img))    and
                     (!empty($description)) and
                     (!empty($brewery_id)) ) {
                        break;
                    }
            }

            // If needed, require new 20 random to test if you have the required fields

        } while ( ( empty($beer_name) ) or
                  ( empty($beer_img) )  or
                  ( empty($description) or
                      empty($brewery_id) )
                );

        $random_beer_data = array();
        $random_beer_data['brewery_id']  = $brewery_id;
        $random_beer_data['beer_name']   = $beer_name;
        $random_beer_data['beer_img']    = $beer_img;
        $random_beer_data['description'] = $description;

        //  brewery/qa1QZU/beers/

        return $random_beer_data;

    }



    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */

    public function search(Application $app, Request $request)
    {

        // Get Json sent by Ajax
        $search_text = $request->request->get('data')['search_text'];
        $_csrf_token = $request->request->get('data')['_csrf_token'];
        $search_type = $request->request->get('data')['search_type'];

        $page        = $request->query->get('p');

        $teste = $app['csrf.token_manager']->isTokenValid(new CsrfToken('token_id', $_csrf_token));  // Check csrf Token

        $body = array();
        $data['numberOfPages'] = 0;
        $data['totalResults']  = 0;
        $data['currentPage']   = 1;

        // Text search validation
        if (Validation::text_validation($search_text)) {

            // Prepare $params requested in BreweryDb API
            $param =  $this->setParameter($page, $randomBrewery = false, $searchBeersByBreweryId = null);

            // Select search type Radio botton value
            if ($search_type == 'option1'){
                $search = 'beers';
            } else {
                $search = 'breweries';
            }

            // Request Brewery API
            $results = $this->requestBreweryDbAPI($search, $search_text, $param);

            $body = $this->prepareBodyresponse($results);

            // Only loads numberOfPages, totalResults, currentPage if any record have been found
            if (isset($results['data'])) {
                $data['numberOfPages'] = $results['numberOfPages'];
                $data['totalResults']  = $results['totalResults'];
                $data['currentPage']   = $results['currentPage'];
            }

        } else {
            $body[] = [
                'name' => 'The searched word is not accepted.. It should only contain letters, numbers, hyphens and spaces.',
                'description' => '',
                'image' => ''
            ];

        }

        $data['data'] = $body;

       if ($body) {
            return $app->json($data, 200, array('Content-Type' => 'application/json'));

        } else {
            return new Response('failure', 200);

        }

    }


    /**
     * @param $page
     * @param null $randomBrewery
     * @param null $searchBeersByBreweryId
     * @return array
     */

    private function setParameter($page = 1, $randomBrewery = false, $searchBeersByBreweryId = null)
    {
        $param         = array();
        $param['p']    = $page;    // Set page to search

        if ($randomBrewery) {
            $param['order']         = 'random';    // Set to random
            $param['randomCount']   = 20;          // Set to return 20 random bear to test which one has all required fields
            $param['withBreweries'] = 'Y';         // Get Brewery information
        }

        return $param;

    }

    /**
     * @param $search_type
     * @param $search_text
     * @param $param
     * @return array
     */

    private function requestBreweryDbAPI($search_type, $search_text = '', $param)
    {

        $breweryService = new Pintlabs\Pintlabs_Service_Brewerydb('5cdb0593177b100ad0f5178d5d0cc942');

        // If search is empty does search on entire base
        if (!empty($search_text)) {
            $param['name'] = '*' . $search_text . '*';

        }

        if ($search_type == 'beers'){
           // If search by beear Set abv parameter for search between from 0% to 100% of alcohol by volume of the beer
            $param['abv']  = '0,100';
        }

        try {
            $results = $breweryService->request($search_type, $param, 'GET');

        } catch (Exception $e) {
            $results = array('error' => $e->getMessage());

        }

        return $results;

    }

    /**
     * @param $results
     * @return array
     */

    private function prepareBodyresponse($results)
    {
        $body = array();

        if (isset($results['data'])) {

            foreach ($results['data'] as $result) {

                if (!empty($result['description'])) {

                    // test if there is image
                    if (!empty($result['labels']['medium'])) {
                        $img = $result['labels']['medium'];

                    } elseif (!empty($result['images']['large'])) {
                        $img = $result['images']['icon'];

                    } else {
                        $img = 'http://placehold.it/48x48/FFFFFF/AAAAAA.png&text=None'; // None image

                    }

                    $body[] = [
                        'name' => $result['name'],
                        'description' => $result['description'],
                        'image' => $img
                    ];

                }

            }

        } else {
            $body[] = [
                'name' => 'No records found in the search. ',
                'description' => '',
                'image' => ''
            ];

        }

        return $body;

    }

    /**
     * @summary Handling Route Errors
     * @param \Exception $e
     * @param Request $request
     * @param $code
     * @return Response|void
     */

    public function error(\Exception $e, Request $request, $code)
    {

        if ($this->app['debug']) {
            return;

        }

        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';

        }

        return new Response($message, $code);

    }

}
