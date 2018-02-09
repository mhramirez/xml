<?
$convertirLetra=new numerosALetras();

$i=0;
$folderfact="";
$cod_raz="";	

//OBTENEMOS DATOS DE LA RAZÒN SOCIAL AL QUE PERTENECE EL LOCAL
	$razon_social = $boleteo->get_razon_social($ConeEdusysNet);

	if($boleteo->local == '01'){
		$folder_fact="pago_xml";
		$cod_raz='09';
	}
	else{
		$folder_fact="pago_xml2";
		$cod_raz='10';
	}

	$boleteo->codrazsoc = $cod_raz;

	$direccion_rs=explode("-",trim($razon_social->Fields('direcrazsoc')));
	$_POST['streetname']=$direccion_rs[0];
	$direc2=explode("N", $direccion_rs[0]);
	$_POST['subdivision_name']=$direc2[0];
	$_POST['ruc']=$razon_social->Fields('rucrazsoc');

	$dni_sin_direccion="";

//OBTENEMOS DATOS DEL PADRE Y/O APODERDA QUE REALIZA LOS PAGOS DEL ALUMNO
	$rs_direcciones = $boleteo->get_datos_responsable_matricula($ConeEdusysNet);
	$direccion_adquiriente=trim($rs_direcciones->Fields('dirrma'));

	if(($direccion_adquiriente=="" or $direccion_adquiriente==null or is_null($direccion_adquiriente))
		and $boleteo->monto_total >=700){
			echo "<span class='alert-danger'>Registre Dirección del Apoderado, es obligatorio, porque el pago es mayor (o igual) a S/ 700</span>";
	}
	else{
		if(($direccion_adquiriente=="" or $direccion_adquiriente==null or is_null($direccion_adquiriente)))
		{
			echo "<span class='alert-warning'>!! Dirección de Apoderado no Registrado</span><br>";
		}

		//DATOS ALUMNO(S)
		$rs_alums = $boleteo->datos_alumno_matriculado($ConeEdusysNet);
		//echo $rs_alums->Fields('codalu');
		//
		//DATOS RESPONSABLE ECONÒMICO
		$rs_res_eco = $boleteo->get_datos_responsable_matricula($ConeEdusysNet);
		$nombre_res_eco = (trim($rs_res_eco->Fields('aperma'))).", ".
							utf8_decode(trim($rs_res_eco->Fields('nomrma')));
		$dni_res_eco = trim($rs_res_eco->Fields('dnirma'));
		$direccion_adquiriente = trim($rs_res_eco->Fields('dirrma'));

		$ubicar_direccion =0;
		if ($direccion_adquiriente=="" or $direccion_adquiriente==null) {
			$ubicar_direccion=0;
		}else{
			$ubicar_direccion=1;
		}

		//GENERAMOS NÚMERO DE BOLETA ELECTRÓNICA
			$boletainf=$boleteo->get_serie_numero_boleta($ConeEdusysNet);	//aca 
			//$boletainf = 'B001-5406';
		//echo($boletainf);
		echo "<span class='alert-info'>Serie Generada ".$boletainf."</span><br>";
		
		//GENERAMOS EL XML 

		$xml  = new DOMDocument('1.0', 'ISO-8859-1');
		$xml->xmlStandalone=false;
		$xml->formatOutput = true;

		$raiz = $xml->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:ccts','urn:un:unece:uncefact:documentation:2');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:ds','http://www.w3.org/2000/09/xmldsig#');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:ext','urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:qdt','urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:sac','urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:udt','urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
		$raiz->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
		$xml->appendChild($raiz);

		$nivel1=$xml->createElement('ext:UBLExtensions');
		$raiz->appendChild($nivel1);

		$nivel2=$xml->createElement('ext:UBLExtension');
		$nivel1->appendChild($nivel2);

		$nivel3=$xml->createElement('ext:ExtensionContent');
		$nivel2->appendChild($nivel3);

		$nivel4=$xml->createElement('sac:AdditionalInformation');
		$nivel3->appendChild($nivel4);

		$nivel5=$xml->createElement('sac:AdditionalMonetaryTotal');
		$nivel4->appendChild($nivel5);

		$nivel6=$xml->createElement('cbc:ID','1003');
		$nivel5->appendChild($nivel6);
		//AQUI ES LA SUMA DE TODOS LOS MONTOS SEGUN TIPO -> OPERACIONES GRAVADAS, INAFECTAS, -.EXONERADAS-.
		$nivel6=$xml->createElement('cbc:PayableAmount',number_format($boleteo->monto_total,2,'.',''));
		$nivel6->setAttributeNS('','currencyID','PEN');
		$nivel5->appendChild($nivel6);

		$nivel5=$xml->createElement('sac:AdditionalProperty');
		$nivel4->appendChild($nivel5);

		$nivel6=$xml->createElement('cbc:ID','1000');
		$nivel5->appendChild($nivel6);
		$nivel6=$xml->createElement('cbc:Value',$convertirLetra->convertir($boleteo->monto_total));
		$nivel5->appendChild($nivel6);


		$nivel2=$xml->createElement('ext:UBLExtension');
		$nivel1->appendChild($nivel2);

		$nivel3=$xml->createElement('ext:ExtensionContent');
		$nivel2->appendChild($nivel3);

		$nivel1=$xml->createElement('cbc:UBLVersionID','2.0');
		$raiz->appendChild($nivel1);

		$nivel1=$xml->createElement('cbc:CustomizationID','1.0');
		$raiz->appendChild($nivel1);

		$nivel1=$xml->createElement('cbc:ID',$boletainf);
		$raiz->appendChild($nivel1);

		$nivel1=$xml->createElement('cbc:IssueDate',date("Y-m-d"));
		$raiz->appendChild($nivel1);
		// en Esta parte ponemos boleta = 03 factura = 01 catalog 01 sunat
		$nivel1=$xml->createElement('cbc:InvoiceTypeCode','03');
		$raiz->appendChild($nivel1);

		$nivel4=$xml->createElement('cbc:ID',$_POST['ruc']);
		$nivel3->appendChild($nivel4);

		$nivel3=$xml->createElement('cac:PartyName');
		$nivel2->appendChild($nivel3);

		$nivel4=$xml->createElement('cbc:Name',trim($razon_social->Fields('desrazsoc')));
		$nivel3->appendChild($nivel4);

		$nivel2=$xml->createElement('cac:DigitalSignatureAttachment');
		$nivel1->appendChild($nivel2);

		$nivel3=$xml->createElement('cac:ExternalReference');
		$nivel2->appendChild($nivel3);

		$nivel4=$xml->createElement('cbc:URI','#signature');
		$nivel3->appendChild($nivel4);

		$nivel1=$xml->createElement('cac:AccountingSupplierParty');
		$raiz->appendChild($nivel1);

		$nivel2=$xml->createElement('cbc:CustomerAssignedAccountID',$_POST['ruc']);
		$nivel1->appendChild($nivel2);

		// En esta parte podemos definir si ruc o DNI 6 RUC  1
		$nivel2=$xml->createElement('cbc:AdditionalAccountID','6');
		$nivel1->appendChild($nivel2);

		$nivel2=$xml->createElement('cac:Party');
		$nivel1->appendChild($nivel2);

		$nivel3=$xml->createElement('cac:PostalAddress');
		$nivel2->appendChild($nivel3);

		$nivel4=$xml->createElement('cbc:ID','100101');
		$nivel3->appendChild($nivel4);

		$nivel4=$xml->createElement('cbc:StreetName',$_POST['streetname']);
		$nivel3->appendChild($nivel4);

		$nivel4=$xml->createElement('cbc:CitySubdivisionName',$_POST['subdivision_name']);
		$nivel3->appendChild($nivel4);

		$nivel4=$xml->createElement('cbc:CityName','HUANUCO');
		$nivel3->appendChild($nivel4);

		$nivel4=$xml->createElement('cbc:CountrySubentity','HUANUCO');
		$nivel3->appendChild($nivel4);

		$nivel4=$xml->createElement('cbc:District','HUANUCO');
		$nivel3->appendChild($nivel4);

		$nivel4=$xml->createElement('cac:Country');
		$nivel3->appendChild($nivel4);

		$nivel5=$xml->createElement('cbc:IdentificationCode','PE');
		$nivel4->appendChild($nivel5);

		$nivel3=$xml->createElement('cac:PartyLegalEntity');
		$nivel2->appendChild($nivel3);

		$nivel4=$xml->createElement('cbc:RegistrationName',trim($razon_social->Fields('desrazsoc')));
		$nivel3->appendChild($nivel4);

		//Cliente
		$nivel1=$xml->createElement('cac:AccountingCustomerParty');
		$raiz->appendChild($nivel1);

		$nivel2=$xml->createElement('cbc:CustomerAssignedAccountID',$dni_res_eco);
		$nivel1->appendChild($nivel2);

		$nivel2=$xml->createElement('cbc:AdditionalAccountID','1');
		$nivel1->appendChild($nivel2);

		$nivel2=$xml->createElement('cac:Party');
		$nivel1->appendChild($nivel2);

		//Direccion del adquiriente
		if ($ubicar_direccion==1) {
			$nivel3=$xml->createElement('cac:PhysicalLocation');
			$nivel2->appendChild($nivel3);

			$nivel4=$xml->createElement('cbc:Description',$direccion_adquiriente);
			$nivel3->appendChild($nivel4);
		}
		$nivel3=$xml->createElement('cac:PartyLegalEntity');
		$nivel2->appendChild($nivel3);

		$nivel4=$xml->createElement('cbc:RegistrationName',$RegistrationName);
		$nivel3->appendChild($nivel4);

		

		//Monto del servicio
		$nivel1=$xml->createElement('cac:LegalMonetaryTotal');
		$raiz->appendChild($nivel1);

		$nivel2=$xml->createElement('cbc:PayableAmount',number_format($boleteo->monto_total,2,'.',''));
		$nivel2->setAttributeNS('','currencyID','PEN');
		$nivel1->appendChild($nivel2);

		$nobj=0;
		$cont=0;


		if($boleteo->monto_mora != 0.00) {
			$nobj=1;
			$mext=array(0=>$boleteo->monto,1=>$boleteo->monto_mora);
			$desconcep=array(0=>$boleteo->concepto,1=>"MORA");
		}else{
			$mext=array(0=>$boleteo->monto);
			$desconcep=array(0=>$boleteo->concepto);
		}

		$desconcep[0]=$desconcep[0]." ".$boleteo->anomat." DE : ";
		while(!$rs_alums->EOF) {
			$utili->campos = "apepat,apemat,nomalu";
			$utili->tabla = "alumno";
			$utili->condicion = "where codalu='".$rs_alums->Fields('codalu')."'";
			$rs_alumno = $utili->get_datos_condicion($ConeEdusysNet);
			$desconcep[0]=$desconcep[0].$simbol.trim($rs_alumno->Fields('apepat'))." ".trim($rs_alumno->Fields('apemat'))." ".strtoupper(trim($rs_alumno->Fields('nomalu'))); //
			$simbol=",";
			$rs_alums->MoveNext();
		}
		$desconcep[0]=$desconcep[0]." , Fecha de deposito ".$utili->f_fecha("d-m-Y",$dia)." con OP =  ".$boleteo->numope." ".$boleteo->desbanco;
		//items de los objetos adquiridos- aqui se hace un for
		while($cont<=$nobj){

			$nivel1=$xml->createElement('cac:InvoiceLine');
			$raiz->appendChild($nivel1);

			$nivel2=$xml->createElement('cbc:ID',$cont+1);
			$nivel1->appendChild($nivel2);

			$nivel2=$xml->createElement('cbc:InvoicedQuantity','1');
			$nivel2->setAttributeNS('','unitCode','ZZ');
			$nivel1->appendChild($nivel2);

			$nivel2=$xml->createElement('cbc:LineExtensionAmount','00.00');
			$nivel2->setAttributeNS('','currencyID','PEN');
			$nivel1->appendChild($nivel2);

			$nivel2=$xml->createElement('cac:PricingReference');
			$nivel1->appendChild($nivel2);

			$nivel3=$xml->createElement('cac:AlternativeConditionPrice');
			$nivel2->appendChild($nivel3);

			$nivel4=$xml->createElement('cbc:PriceAmount',$mext[$cont]);
			$nivel4->setAttributeNS('','currencyID','PEN');
			$nivel3->appendChild($nivel4);

			$nivel4=$xml->createElement('cbc:PriceTypeCode','01');
			$nivel3->appendChild($nivel4);

			$nivel2=$xml->createElement('cac:TaxTotal');
			$nivel1->appendChild($nivel2);

			$nivel3=$xml->createElement('cbc:TaxAmount','00.00');
			$nivel3->setAttributeNS('','currencyID','PEN');
			$nivel2->appendChild($nivel3);

			$nivel3=$xml->createElement('cac:TaxSubtotal');
			$nivel2->appendChild($nivel3);

			$nivel4=$xml->createElement('cbc:TaxAmount','00.00');
			$nivel4->setAttributeNS('','currencyID','PEN');
			$nivel3->appendChild($nivel4);

			$nivel4=$xml->createElement('cac:TaxCategory');
			$nivel3->appendChild($nivel4);

			$nivel5=$xml->createElement('cbc:TaxExemptionReasonCode','20');
			$nivel4->appendChild($nivel5);

			$nivel5=$xml->createElement('cac:TaxScheme');
			$nivel4->appendChild($nivel5);

			$nivel6=$xml->createElement('cbc:ID','1000');
			$nivel5->appendChild($nivel6);

			$nivel6=$xml->createElement('cbc:Name','IGV');
			$nivel5->appendChild($nivel6);

			$nivel6=$xml->createElement('cbc:TaxTypeCode','VAT');
			$nivel5->appendChild($nivel6);

			$nivel2=$xml->createElement('cac:Item');
			$nivel1->appendChild($nivel2);

			$nivel3=$xml->createElement('cbc:Description',trim($desconcep[$cont]));
			$nivel2->appendChild($nivel3);


			$nivel2=$xml->createElement('cac:Price');
			$nivel1->appendChild($nivel2);

			$nivel3=$xml->createElement('cbc:PriceAmount',$mext[$cont]);
			$nivel3->setAttributeNS('','currencyID','PEN');
			$nivel2->appendChild($nivel3);

			$cont++;
		}
		//Fin Acá
		
		
		//Acaa
		
		$xml->saveXML();

		// Tenemos que crear una carpeta factura
		$xml->save('../../../documento/pago_xml/'.$_POST['ruc'].'/boleta/DATA/'.$_POST['ruc'].'-03-'.$boletainf.'.xml');
		
		// Podemos crear rutas distintas para boletas y facturas en el server
		$boleteo->send_xml_smb_windows_server('../../../documento/pago_xml/'.$_POST['ruc'].'/boleta/DATA/'.$_POST['ruc'].'-03-'.$boletainf.'.xml'
			,$_POST['ruc'],$boletainf);

		echo "<span class='alert-success'>Boleta Generada y Enviada</span><br>";

		if ($cod_raz=='09') {

			$boleteo->update_tbl_boletaf1($boletainf,$dni_res_eco,$ConeEdusysNet);
		}
		//echo($secuencias_aulas[$i]);
		//echo "------";
		$sec_updt=explode('-', $boleteo->secuencia_pago);
		$odt=0;
		while ($odt <= sizeof($sec_updt)) {
			$boleteo->actualizar_informacion_boleta($boletainf,$dni_res_eco,$sec_updt[$odt],
													$cod_raz,$ConeEdusysNet);
			$odt++;
		}
		echo "<span class='alert-info'>Datos de Boletas actualizados</span>";
	}
?>