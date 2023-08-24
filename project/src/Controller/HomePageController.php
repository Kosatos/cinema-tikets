<?php

namespace App\Controller;

use App\Entity\Hall;
use App\Entity\Cinema;
use DateTimeImmutable;
use App\Entity\Session;
use App\Repository\HallRepository;
use App\Repository\CinemaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
	#[Route('/', name: 'homepage')]
	public function index(CinemaRepository $cinemaRepository, HallRepository $hallRepository): Response
	{
		$data = (new DateTimeImmutable())->format('Y-m-d');

		$currentDataFilms = array_filter($cinemaRepository->findAll(), fn(Cinema $cinema) => $cinema->getSessions()
				->filter(fn(Session $session) => $session->getData()->format('Y-m-d') === $data)->count() > 0
		);

		$films = array_map(fn(Cinema $cinema) => [
			'cinema' => $cinema,
			'halls' => array_map(fn(Hall $hall) => [
				'hall' => $hall->getNumber(),
				'sessions' => array_filter($hall->getSessions()->toArray(), fn(Session $session) => $session->getHall()->getNumber() == $hall->getNumber() && $session->getData()->format('Y-m-d') == $data),
			], $hallRepository->findAll())
		], $currentDataFilms);

		return $this->render('pages/home.html.twig', compact('films'));
	}
}
