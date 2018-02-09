<?php
  crear(); //Creamos el archivo
  leer();  //Luego lo leemos
 
  //Para crear el archivo

  
  function crear(){

  	$documento='';
  	$tipo = '08';
  	if($tipo=='07'){
  		$documento = '03';
  	}elseif ($tipo=='08'){

  		$documento='01';

  		$ruc_cli = '10469722398';
  		$Razon_cli = 'Max Ramirez Martel';
  		$direccion_cli = 'Jr. Independencia #1494';
  	}

  	echo $documento;


  	$boletainf = 'B001-5406';
    $xml = new DomDocument('1.0', 'UTF-8');    
 
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

	$nivel6=$xml->createElement('cbc:PayableAmount',number_format(450,2,'.',''));
	$nivel6->setAttributeNS('','currencyID','PEN');
	$nivel5->appendChild($nivel6);

	$nivel5=$xml->createElement('sac:AdditionalProperty');
	$nivel4->appendChild($nivel5);

	$nivel6=$xml->createElement('cbc:ID','1000');
	$nivel5->appendChild($nivel6);
	$nivel6=$xml->createElement('cbc:Value','CUATROCIENTOS CON 00/100');
	$nivel5->appendChild($nivel6);


	$nivel2=$xml->createElement('ext:UBLExtension');
	$nivel1->appendChild($nivel2);

	$nivel3=$xml->createElement('ext:ExtensionContent');
	$nivel2->appendChild($nivel3);

	$nivel1=$xml->createElement('cbc:UBLVersionID','2.1');
	$raiz->appendChild($nivel1);

	$nivel1=$xml->createElement('cbc:CustomizationID','2.0');
	$raiz->appendChild($nivel1);

	$nivel1=$xml->createElement('cbc:ID',$boletainf);
	$raiz->appendChild($nivel1);

	$nivel1=$xml->createElement('cbc:IssueDate',date("Y-m-d"));
	$raiz->appendChild($nivel1);

	// verificamos si es factura o boleta 01= factura 03=boleta
	if($documento=='01'){

    	$nivel1=$xml->createElement('cbc:InvoiceTypeCode','01');
		$raiz->appendChild($nivel1);

    }elseif($documento=='03') {    	

    	$nivel1=$xml->createElement('cbc:InvoiceTypeCode','03');
		$raiz->appendChild($nivel1);
    }


    // $nivel4=$xml->createElement('cbc:ID',$_POST['ruc']);
    $nivel4=$xml->createElement('cbc:ID','20601728657');
	$nivel3->appendChild($nivel4);

	$nivel3=$xml->createElement('cac:PartyName');
	$nivel2->appendChild($nivel3);

	// $nivel4=$xml->createElement('cbc:Name',trim($razon_social->Fields('desrazsoc')));
	$nivel4=$xml->createElement('cbc:Name','INVERSIONES COMAFE E.I.R.L');
	$nivel3->appendChild($nivel4);

	$nivel2=$xml->createElement('cac:DigitalSignatureAttachment');
	$nivel1->appendChild($nivel2);

	$nivel3=$xml->createElement('cac:ExternalReference');
	$nivel2->appendChild($nivel3);

	$nivel4=$xml->createElement('cbc:URI','#signature');
	$nivel3->appendChild($nivel4);

	$nivel1=$xml->createElement('cac:AccountingSupplierParty');
	$raiz->appendChild($nivel1);

	$nivel2=$xml->createElement('cbc:CustomerAssignedAccountID','20601728657');
	$nivel1->appendChild($nivel2);

	$nivel2=$xml->createElement('cbc:CustomerAssignedAccountID','no se que estamos cambiando');
	$nivel1->appendChild($nivel2);





    $xml->formatOutput = true;
    $el_xml = $xml->saveXML();
    if($documento=='01'){
    	$xml->save('facturas/libros.xml');
    }elseif ($documento=='03') {
    	$xml->save('boletas/libros.xml');
    }	
    //Mostramos el XML puro
    echo "<p><b>El XML ha sido creado.... Mostrando en texto plano:</b></p>".
    htmlentities($el_xml)."<br/><hr>";
  }
 
  //Para leerlo
  function leer(){
    echo "<p><b>Ahora mostrandolo con estilo</b></p>";
    $xml = simplexml_load_file('libros.xml');
    $salida ="";
    foreach($xml->libro as $item){
      $salida .=
        "<b>Autor:</b> " . $item->autor . "<br/>".
        "<b>Título:</b> " . $item->titulo . "<br/>".
        "<b>Ano:</b> " . $item->anio . "<br/>".
        "<b>Editorial:</b> " . $item->editorial . "<br/><hr/>";
    }
    echo $salida;
  }
?>