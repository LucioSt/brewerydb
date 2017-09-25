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



        // CHAMAR RANDOM GENERATE






        return $app['twig']->render('index.html.twig', [
            'csrf_token' => $csrf_token,
        ]);

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

            // Request Brewery API
            $results = $this->requestBreweryDbAPI($search_type, $search_text, $page);

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
     * @param $search_type
     * @return array
     */

    public function requestBreweryDbAPI($search_type, $search_text, $page)
    {

        $breweryService = new Pintlabs\Pintlabs_Service_Brewerydb('088f6bdfeec1fb150ca23a68be733e2c');
        $param          = array();
        $param['p']     = $page;    // Set page to search

        // If search is empty does search on entire base
        if (!empty($search_text)) {
            $param['name'] = '*' . $search_text . '*';

        }

        if ($search_type == 'option1'){
            $search = 'beers';

            // If search by beear Set abv parameter for search between from 0% to 100% of alcohol by volume of the beer
            $param['abv']  = '0,100';

        } else
        {
            $search = 'breweries';
        }

        try {
            $results = $breweryService->request($search, $param, 'GET');

        } catch (Exception $e) {
            $results = array('error' => $e->getMessage());

        }

        return $results;

    }

    /**
     * @param $results
     * @return array
     */

    public function prepareBodyresponse($results)
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
