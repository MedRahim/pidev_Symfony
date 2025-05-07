<?php

namespace App\Controller\Ines;

use Google\Client;
use Google\Service\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleCalendarController extends AbstractController
{
    #[Route('/google/calendar', name: 'app_google_calendar')]
    public function readPublicCalendar(): Response
    {
        $client = new Client();
        $client->setApplicationName('My Public Calendar App');
        //$client->setDeveloperKey('AIzaSyAXb6Xg07Lj8WUojmnDp573dDU77Qt4yJY'); // 🔑 Remplace par ta vraie clé API

        $service = new Calendar($client);

        // 🗓️ Exemple de calendrier public (jours fériés en France)
        //$calendarId = 'ar.tn#holiday@group.v.calendar.google.com';


        $optParams = [
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        ];

        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        return $this->render('BackOffice/calendar.html.twig', [
            'events' => $events,
        ]);
    }
}
