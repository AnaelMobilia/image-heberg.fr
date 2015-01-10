<?php
/*
* Copyright 2008-2015 Anael Mobilia
*
* This file is part of NextINpact-Unofficial.
*
* NextINpact-Unofficial is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextINpact-Unofficial is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextINpact-Unofficial. If not, see <http://www.gnu.org/licenses/>
*/
//**************************************
//	./includes/mail.php
//	Librairie - mails
//**************************************
/**
 * V�rifie une adresse mail (type + mx + socket)
 *
 * @param string $mail : mail � v�rifier
 * @return boolean : adresse valid�e ?
 */
function validate_email($mail)
{
	//Supprime tous les caract�res sauf les lettres, chiffres, et !#$%&'*+-/=?^_`{|}~@.[].
	$mail = filter_var($mail, FILTER_SANITIZE_EMAIL);

	if(filter_var($mail, FILTER_VALIDATE_EMAIL))	//mail reconnu comme valide par PHP ?
	{
		//v�rification du domaine
		$domaine = strstr($mail, "@");	//@domaine
		$domaine = substr($domaine, 1);	//suppression du @
		if(checkdnsrr($domaine, "MX"))	//le domaine a-t-il des enregistrement MX ?
		//boolean
		{
			$check = TRUE;
		}
		/*
		bool getmxrr  ( string $hostname  , array &$mxhosts  [, array &$weight  ] )
		$domaine							retour mx						nb d'enregistrements retourn�s
		if(getmxrr($domaine, $mx_hosts, $mx_nb))	//on r�cup�re les enregistrements DNS pour les MX
		{

		}
		else
		$check = FALSE;
		ouvrir connexion : fsockopen($smtpserver, 25, $errnumber, $errstring, $time_out); err... use en cas de pb
		fputs / fgets
		root@anael-vm:/home/anael/boulet_fevrier# telnet mx.google.com 25
		Trying 209.85.219.57...
		Connected to 209.85.219.57.
		Escape character is '^]'.
		220 mx.google.com ESMTP 25si1563718ewy.19
		ehlo vm.anael.eu
		250-mx.google.com at your service, [94.23.137.36]
		250-SIZE 35651584
		250-8BITMIME
		250-ENHANCEDSTATUSCODES
		250 PIPELINING
		mail from:<anael@free-h.org>
		250 2.1.0 OK 25si1563718ewy.19
		rcpt to:<anael@gmail.com>
		550-5.1.1 The email account that you tried to reach does not exist. Please try
		550-5.1.1 double-checking the recipient's email address for typos or
		550-5.1.1 unnecessary spaces. Learn more at
		550 5.1.1 http://mail.google.com/support/bin/answer.py?answer=6596 25si1563718ew                                                                             y.19
		rcpt to:<papa.nawell@gmail.com>
		250 2.1.5 OK 25si1563718ewy.19
		quit
		221 2.0.0 closing connection 25si1563718ewy.19
		Connection closed by foreign host.
		*/
		else
		{
			$check = FALSE;
		}
	}
	else
	{
		$check = FALSE;
	}

	//retourne la d�cision - bool�ene
	return $check;
}