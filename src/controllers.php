<?php

namespace Brewerydb;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\Provider\CsrfServiceProvider;
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

        $app->get('/foo/{name}', [$this, 'teste'])
            ->bind('teste');


        return $controllers;
    }


    public function homepage(Application $app)
    {

        $csrf_token = $app['csrf.token_manager']->getToken('token_id');

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
        $page        = $request->query->get('p');

        $teste = $app['csrf.token_manager']->isTokenValid(new CsrfToken('token_id', $_csrf_token));  // Check csrf Token

        $breweryService = new Pintlabs\Pintlabs_Service_Brewerydb('088f6bdfeec1fb150ca23a68be733e2c');
        $param = array();
        $param['abv']  = '0,100';  // Set abv parameter for search between from 0% to 100% of  alcohol by volume of the beer
        $param['p']    = $page;

        if (!empty($search_text)){
            $param['name'] = '*'.$search_text.'*';
        }

        try {
            $results = $breweryService->request('beers', $param, 'GET'); // where $params is a keyed array of parameters to send with the API call.
        } catch (Exception $e) {
            $results = array('error' => $e->getMessage());
        }

        $body = array();

        foreach ($results['data'] as $result ) {

            if (!empty($result['description'])) {

                // test if there is image
                if (!empty($result['labels']['medium'])) {
                    $img = $result['labels']['medium'];
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

        $data['currentPage']   = $results['currentPage'];
        $data['numberOfPages'] = $results['numberOfPages'];
        $data['totalResults']  = $results['totalResults'];
        $data['data']          = $body;

       if ($body) {
            return $app->json($data, 200, array('Content-Type' => 'application/json'));
        } else {
            return new Response('failure', 200);
        }

    }

    /**
     * @param Application $app
     * @param $name
     * @return mixed
     */

    public function teste(Application $app, $name)
    {
        return $app['twig']->render('teste.html.twig', array(
            'name' => $name,
        ));
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
