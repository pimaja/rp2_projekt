<?php

class UsersController extends BaseController
{
	public function index()
	{

	}

	public function odlogiraj()
	{
		unset($_POST['ime']); unset($_POST['prezime']); unset($_POST['pass']);
		session_unset(); session_destroy();
		//$this->registry->template->show( 'login' );
		header( 'Location: ' . __SITE_URL . '/index.php?rt=start/index');
	}

	public function odabir()
	{
		$this->registry->template->title = 'Odaberi!';
		$this->registry->template->show( 'odabir' );
	}

	public function check_login()
	{
		$ss = new SmjestajService();
		if(isset($_POST['ime'], $_POST['prezime']))
			$lozinka = $ss->getPasswordByNameAndSurname( $_POST['ime'], $_POST['prezime'] );
		if(isset( $_POST['pass'] ))
			if(password_verify($_POST['pass'], $lozinka))
			{
				session_start();
				$secret_word = 'racunarski praktikum 2!!!';
				$_SESSION['login'] = $_POST['ime'] . ','. $_POST['prezime'] . ',' . md5( $_POST['ime'] . $secret_word );;
				header( 'Location: ' . __SITE_URL . '/index.php?rt=users/odabir');
				exit();
			}
			else $this->odlogiraj();
		else $this->odlogiraj();
	}

  public function check_register()
	{
		if(isset( $_POST['pass'] ) && isset( $_POST['pass2'] ) && $_POST['pass'] === $_POST['pass2'] )
		{
			$ss2 = new SmjestajService();
			$ss2->dodajUsera($_POST['ime'], $_POST['prezime'], $_POST['pass']);
			session_start();
			$secret_word = 'racunarski praktikum 2!!!';
			$_SESSION['login'] = $_POST['ime'] . ',' . $_POST['prezime'] . ',' . md5( $_POST['ime'] . $secret_word );;
			header( 'Location: ' . __SITE_URL . '/index.php?rt=users/odabir');
			exit();
		}
		else $this->odlogiraj();
	}

	public function check_odabir()
	{
		if(isset($_POST['odlogiraj']))
		{
			$this->odlogiraj();
			exit();
		}
		if(isset($_POST['odabir']))
		{
			$_SESSION['ime_grada'] = $_POST['odabir'];
			$this->registry->template->title = 'Sortiraj i filtriraj!';
			$this->registry->template->show( 'sortiraj_filtriraj' );
			//exit();
			//sad treba omogućiti da pretrazuje hotele po filerima(udaljenost, vlastita soba...) ili sortira(po cijeni...)
		}
	}

	public function check_sort_filt()
	{
		if(isset($_POST['odlogiraj']))
		{
			$this->odlogiraj();
			exit();
		}
		if(isset($_POST['natrag']))
		{
			$this->registry->template->title = 'Odaberite grad koji Vas zanima';
			$this->registry->template->show( 'odabir' );
			unset(	$_SESSION['sort']); unset($_SESSION['filter']);
			unset($_SESSION['cijena']); unset($_SESSION['udaljenost']);
			unset($_SESSION['osobe']); unset($_SESSION['bracni']);
			unset($_SESSION['odvojeni']); unset($_SESSION['na_kat']);
			unset($_SESSION['ocjena']); unset($_SESSION['zvjezdice']);
			exit();
		}
		//postavljanje sessiona, za slučaj da se vratimo sa stranice detalji natrag na
		//sortirano, filtrirano da možemo opet dobiti isti ispis kao i nakon stranice
		// sortiraj i filtriraj... U tom slučaju nemamo postove...
		if(isset($_POST['sort']))
			$_SESSION['sort'] = $_POST['sort'];
		if(isset($_POST['filter']))
			$_SESSION['filter'] = $_POST['filter'];
		if(isset($_POST['cijena']))
			$_SESSION['cijena'] = $_POST['cijena'];
		if(isset($_POST['udaljenost']))
			$_SESSION['udaljenost'] = $_POST['udaljenost'];
		if(isset($_POST['osobe']))
			$_SESSION['osobe'] = $_POST['osobe'];
		if(isset($_POST['bracni']))
			$_SESSION['bracni'] = $_POST['bracni'];
		if(isset($_POST['odvojeni']))
			$_SESSION['odvojeni'] = $_POST['odvojeni'];
		if(isset($_POST['na_kat']))
			$_SESSION['na_kat'] = $_POST['na_kat'];
		if(isset($_POST['ocjena']))
			$_SESSION['ocjena'] = $_POST['ocjena'];
		if(isset($_POST['zvjezdice']))
			$_SESSION['zvjezdice'] = $_POST['zvjezdice'];

		$polje_polja_hotela= array();
		$hotel_kriteriji= array();
		$filtri = array();
		if(isset($_SESSION['sort']))
		{
			$ss3 = new SmjestajService();
				$N = count($_SESSION['sort']);
				for($i=0; $i < $N; $i++)
	    	{
						$polje_hotela = $ss3->getHotelsByNameOrderBy($_SESSION['ime_grada'], $_SESSION['sort'][$i]);
						array_push($polje_polja_hotela, $polje_hotela);
						array_push($hotel_kriteriji, $_SESSION['sort'][$i]);
				}
	}
		else {
			$ss3 = new SmjestajService();
			$polje_hotela = $ss3->getHotelsByName($_SESSION['ime_grada']);
			array_push($polje_polja_hotela, $polje_hotela);
		}
			if(isset($_SESSION['filter']))
			{
				$M = count($_SESSION['filter']);
				for($i=0; $i < $M; $i++)
				{
					if($_SESSION['filter'][$i] === 'cijena_po_osobi' && isset($_SESSION['cijena']))
					{
						$ss3->applyFilterCijena($polje_polja_hotela, $_POST['cijena']);
						array_push($filtri, 'cijena po osobi po noćenju najviše: '.$_SESSION['cijena']);
					}
					if($_SESSION['filter'][$i] === 'udaljenost_od_centra' && isset($_SESSION['udaljenost']))
					{
						$ss3->applyFilterUdaljenost($polje_polja_hotela, $_SESSION['udaljenost']);
						array_push($filtri, 'udaljenost od centra najviše: '.$_SESSION['udaljenost']);
					}
					if($_SESSION['filter'][$i] === 'broj_osoba' && isset($_SESSION['osobe']))
					{
						$ss3->applyFilterOsobe($polje_polja_hotela, $_SESSION['osobe']);
						array_push($filtri, 'broj osoba: '.$_SESSION['osobe']);
					}
					if($_SESSION['filter'][$i] === 'tip_kreveta' && (isset($_POST['bracni'])
					|| isset($_SESSION['odvojeni']) || isset($_SESSION['na_kat'])))
					{
						$nizKreveti = array();
						if($_SESSION['bracni'] !== '' && $_SESSION['bracni'] !== '0')
							array_push($nizKreveti, $_SESSION['bracni'].' x bracni');
						if($_SESSION['odvojeni'] !== '' && $_SESSION['odvojeni'] !== '0')
							array_push($nizKreveti, $_SESSION['odvojeni'].' x odvojeni');
						if($_SESSION['na_kat'] !== '' && $_SESSION['na_kat'] !== '0')
							array_push($nizKreveti, $_SESSION['na_kat'].' x na kat');
						$string = ' ';
						foreach($nizKreveti as $var) $string.= $var.' ';
						$ss3->applyFilterKreveti($polje_polja_hotela, $nizKreveti);
						array_push($filtri, 'tip kreveta: '.$string);
					}
					if($_SESSION['filter'][$i] === 'ocjena' && isset($_SESSION['ocjena']))
					{
						$ss3->applyFilterOcjena($polje_polja_hotela, $_SESSION['ocjena']);
						array_push($filtri, 'minimalna ocjena: '.$_SESSION['ocjena']);
					}
					if($_SESSION['filter'][$i] === 'broj_zvjezdica' && isset($_SESSION['zvjezdice']))
					{
						$ss3->applyFilterZvjezdice($polje_polja_hotela, $_SESSION['zvjezdice']);
						array_push($filtri, 'minimalni broj zvjezdica: '.$_SESSION['zvjezdice']);
					}
					if($_SESSION['filter'][$i] === 'vlastita_kupaonica')
					{
						$ss3->applyFilterKupaonica($polje_polja_hotela);
						array_push($filtri, 'obvezna kupaonica' );
					}
				}
			}

			$sort_cijene = array();
			$sort_osobe = array();
			$i=0;
			$niz_id = array();
			foreach($polje_polja_hotela as $polje_hotela)
			{
				if(isset($hotel_kriteriji[$i]) && $hotel_kriteriji[$i] === 'cijena_po_osobi')
				{
					foreach($polje_hotela as $hotel)
						array_push($niz_id, $hotel->id);
					array_unique($niz_id);
					foreach($polje_hotela as $hotel)
					{
						if(in_array($hotel->id, $niz_id))
							foreach($hotel->sobe as $soba)
							{
								array_push($sort_cijene, $soba->cijena_po_osobi);
								unset($niz_id[array_search($hotel->id, $niz_id)]);
							}
					}
				}
				if(isset($hotel_kriteriji[$i]) && $hotel_kriteriji[$i] === 'broj_osoba')
				{
					foreach($polje_hotela as $hotel)
						array_push($niz_id, $hotel->id);
					array_unique($niz_id);
					foreach($polje_hotela as $hotel)
					{
						if(in_array($hotel->id, $niz_id))
							foreach($hotel->sobe as $soba)
							{
								array_push($sort_osobe, $soba->broj_osoba);
								unset($niz_id[array_search($hotel->id, $niz_id)]);
							}
					}
				}
				$i++;
			}
			sort($sort_cijene); sort($sort_osobe);
			$this->registry->template->hoteli = $polje_polja_hotela;
			$this->registry->template->hotel_kriteriji = $hotel_kriteriji;
			$this->registry->template->filtri = $filtri;
			$this->registry->template->sort_cijene = $sort_cijene;
			$this->registry->template->sort_osobe = $sort_osobe;
			$this->registry->template->title = 'Sortiraj i filtriraj!';
			$this->registry->template->show( 'sortirano_filtrirano' );

	}

	public function check_details()
	{
		if(isset($_POST['odlogiraj']))
		{
			$this->odlogiraj();
			exit();
		}

		if(isset($_POST['natrag']))
		{
			$this->registry->template->title = 'Odaberite kako želite da Vam hoteli budu sortirani i filtrirani';
			$this->registry->template->show( 'sortiraj_filtriraj' );
			unset($_SESSION['detalji']);
			unset($_SESSION['id_hotela']);
			exit();
		}
			$ss = new SmjestajService();
			$polje_hotela = $ss->getHotelsByName($_SESSION['ime_grada']);

			foreach($polje_hotela as $hotel)
			{
				if((isset($_POST['detalji']) && $_POST['detalji'] === $hotel->ime_hotela) ||
				(isset($_SESSION['detalji']) && $_SESSION['detalji'] === $hotel->ime_hotela))
				{
					$_SESSION['detalji'] = $hotel->ime_hotela;
					$_SESSION['id_hotela'] = $hotel->id;
					$polje_komentara = $ss->getCommentsByHotelId($hotel->id);
					$this->registry->template->title = 'Detalji hotela';
					$this->registry->template->hotel = $hotel;
					$this->registry->template->komentari = $polje_komentara;
					$this->registry->template->show( 'detalji' );
					exit();
				}
			}

	}

	public function check_comments()
	{
		if(isset($_POST['odlogiraj']))
		{
			$this->odlogiraj();
			exit();
		}

		if(isset($_POST['natrag']))
		{
			unset($_POST['natrag']);
			$this->check_sort_filt();
			exit();
		}

		if(isset($_POST['komentar']))
		{
			//Tu sad jos dodat komentar u bazu i prebacit opet
			//na fju $this->check_details(); da se ispise i novi komentar...
			//IL TO SPADA POD KOMUNIKACIJU PA ĆEMO TO S JAVASCRIPTOM, lako se dopiše
			//ovo ako ćemo s php...
		}
	}

};

?>
