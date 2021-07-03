<?php
#
# database tables
#
namespace SIMPELBOEK;

class Dbtables
{
	const boekhoudingen = ["name"=>"sbh_boekhoudingen", "columns"=>"
	    `id` int(10) NOT NULL AUTO_INCREMENT,
		`code` varchar(10) NOT NULL,
		`naam` varchar(50) NOT NULL,
		`boekjaar` int(4) NOT NULL,
		UNIQUE KEY (`code`),
		PRIMARY KEY (`id`)"];

    const rekeningen = ["name"=>"sbh_rekeningen", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
		`naam` varchar(50) NOT NULL, 
		`bankrekening` varchar(20) NOT NULL,
		`rekeningnummer` varchar(4) NOT NULL,
		`soort` varchar(1) NOT NULL,
		`type` varchar(1) NOT NULL,
        `btwpercentage` INT NOT NULL,
	    UNIQUE KEY (`rekeningnummer`),
		PRIMARY KEY (`id`)"];

	const balans = ["name"=>"sbh_balans", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
        `rekeningnummer` varchar(4) NOT NULL,
       `boekjaar` varchar(4) NOT NULL,
       `bedrag` varchar(10) NOT NULL,
        PRIMARY KEY (`id`)"];

	const begroting = ["name"=>"sbh_begroting", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
	    `rekeningnummer` varchar(4) NOT NULL,
		`boekjaar` varchar(4) NOT NULL,
		`bedrag` varchar(10) NOT NULL,
		PRIMARY KEY (`id`)"];

	const boekingen = ["name"=>"sbh_boekingen", "columns"=>"
        `id` int(10) NOT NULL AUTO_INCREMENT, 
		`datum`date NOT NULL,
		`bedrag` varchar(10) NOT NULL,
		`btw` varchar(10) NOT NULL,
		`type` varchar(1) NOT NULL,
		`rekening` varchar(4) NOT NULL,
		`tegenrekening` varchar(4) NOT NULL,
		`referentie` varchar(255) NOT NULL,
		`bankrekening` varchar(20) NOT NULL,
		`bankrekeninghouder` varchar(255) NOT NULL,
		`omschrijving` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)"];
}
?>