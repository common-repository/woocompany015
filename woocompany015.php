<?php
/*
Plugin Name: WooCompany015
Plugin URI: https://www.company015.it/woocommerce/
Description: integrazione WooCommerce con COMPANY015
Author: MEDIA PROMOTION SRL
Version: 1.1.0
Author URI: https://www.mediapromotion.it/
*/

$uploaddir=trailingslashit( WP_CONTENT_DIR ).'uploads/woocompany015';
if (!is_dir($uploaddir) && !mkdir($uploaddir)){ echo "error dir";}
define( 'WOOCOMPANY_COMPANY015_ENDPOINT', 'https://cmp015.it' );
define( 'WOOCOMPANY_COMPANY015_LOGFILE', $uploaddir.'/woocompany015log.txt' );

add_action( 'admin_init', 'woocompany_settings_init' );
add_action('admin_menu', 'woocompany_menu_pages');

function woocompany_menu_pages(){
    add_menu_page('WooCompany015', 'WooCompany015', 'manage_options', 'woocompany-menu', 'woocompany_menu_output' );
    add_submenu_page('woocompany-menu', 'Configurazione', 'Configurazione', 'manage_options', 'woocompany-menu', 'woocompany_options_page' );
    add_submenu_page('woocompany-menu', 'Registro Eventi', 'Registro Eventi', 'manage_options', 'woocompany-menu2', 'woocompany_logs_page' );
    add_submenu_page('woocompany-menu', 'Aiuto', 'Aiuto', 'manage_options', 'woocompany-menu3', 'woocompany_help_page' );
}
/*settings*/
add_filter( 'plugin_action_links_woocompany015/woocompany015.php', 'woocompany_settings_link' );
function woocompany_settings_link( $links ) {
	$url = esc_url( add_query_arg(
		'page',
		'woocompany-menu',
		get_admin_url() . 'admin.php'
	) );
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	array_push(
		$links,
		$settings_link
	);
	return $links;
}
/*end settings*/

/*help*/
add_filter( 'plugin_action_links_woocompany015/woocompany015.php', 'woocompany_help_link' );
function woocompany_help_link( $links ) {
	$url = esc_url( add_query_arg(
		'page',
		'woocompany-menu3',
		get_admin_url() . 'admin.php'
	) );
	$settings_link = "<a href='$url'>" . __( 'Help' ) . '</a>';
	array_push(
		$links,
		$settings_link
	);
	return $links;
}
/*end Help*/


function woocompany_settings_init(  ) {
    register_setting( 'woocompanyPlugin', 'woocompany_settings' );

    //SEZIONE WOOCOMPANY
    add_settings_section(
        'woocompany_config_section',
        __( 'WooCompany015', 'wordpress' ),
        'woocompany_settings_section_callback',
        'woocompanyPlugin'
    );

    add_settings_field(
        'woocompany_kapi',
        __( 'chiave company015', 'wordpress' ),
        'woocompany_field_render',
        'woocompanyPlugin',
        'woocompany_config_section',array("id"=>'woocompany_kapi')
    );

    add_settings_field(
        'woocompany_module',
        __( 'codice account', 'wordpress' ),
        'woocompany_field_render',
        'woocompanyPlugin',
        'woocompany_config_section',array("id"=>'woocompany_module')
    );

    add_settings_field(
        'woocompany_seldoc',
        __( 'Documento da creare', 'wordpress' ),
        'woocompany_field_render',
        'woocompanyPlugin',
        'woocompany_config_section',array("id"=>'woocompany_seldoc')
    );

    add_settings_field(
        'woocompany_website',
        __( 'Sito Web', 'wordpress' ),
        'woocompany_field_render',
        'woocompanyPlugin',
        'woocompany_config_section',array("id"=>'woocompany_website')
    );

    //SEZIONE WOOCOMMERCE
    add_settings_section(
        'woocompany_config_section2',
        __( 'WooCommerce', 'wordpress' ),
        'woocompany_settings_section_callback',
        'woocompanyPlugin'
    );

    add_settings_field(
        'woocompany_woosecretkey',
        __( 'Chiave Cliente', 'wordpress' ),
        'woocompany_field_render',
        'woocompanyPlugin',
        'woocompany_config_section2',array("id"=>'woocompany_woosecretkey')
    );

    add_settings_field(
        'woocompany_woosecretuser',
        __( 'Utente Nascosto', 'wordpress' ),
        'woocompany_field_render',
        'woocompanyPlugin',
        'woocompany_config_section2',array("id"=>'woocompany_woosecretuser')
    );


}

function woocompany_field_render( $args ) {
    $options = get_option( 'woocompany_settings' );
    $id=$args["id"];
    if($id=="woocompany_endpoint"){
        if($options[$id]=="") $options[$id]="https://cmp015.it";
    }
    if($id=="woocompany_website"){
        if($options[$id]=="") $options[$id]=get_site_url();
    }
    if($id=="woocompany_seldoc"){
        ?>
        <select name='woocompany_settings[<?php echo $id; ?>]'>
            <option value='0' <?php selected( $options[$id], 0 ); ?>>crea ordine</option>
            <option value='1' <?php selected( $options[$id], 1 ); ?>>crea fattura</option>
        </select>

        <?php
    }
    else{
         ?>
         <input class='regular-text ltr'  type='text' name='woocompany_settings[<?php echo $id; ?>]' value='<?php echo $options[$id]; ?>'>
        <?php
     }
}

function woocompany_settings_section_callback(  ) {

}
function woocompany_menu_output(  ) {

}

function woocompany_options_page(  ) {
    ?>
    <?php

    $url = esc_url( add_query_arg(
      'page',
      'woocompany-menu3',
      get_admin_url() . 'admin.php'
    ) );
    $settings_link = "<a href='$url'>" . __( 'Help' ) . '</a>';

    $errors="";
    $confirms="";

    if(isset($_POST['action']) && ($_POST['action']=="woocompany_auth")){
        $response=woocompany_wauthcheck();
        if(isset($response->result)){
            if($response->result=="1"){
                $confirms="&#9989; Attivazione avvenuta con successo, WooCompany015 e' <b>pronto</b> per l'utilizzo";
            }else{
                if($response->coderesult=="705")
                    $errors="&#x274C; Errore: chiave non abilitata, il valore 'chiave company015' non corrisponde al valore 'chiave di attivazione in COMPANY015' - ".$settings_link;
                else
                    $errors="&#x274C; Errore: coderesult=" .$response->coderesult;
            }
        }else{
            $errors="&#x274C; Errore connessione al server";
        }
    }
    if(isset($_POST['action']) && ($_POST['action']=="woocompany_auth2")){
        $confirms="Operazione completata";
    }
    ?>

    <?php if(strlen($errors)>0){ ?>
        <div class="error notice">
            <p><?php echo $errors; ?></p>
        </div>
    <?php } ?>
    <?php if(strlen($confirms)>0){ ?>
        <div class="updated notice">
            <p><?php echo $confirms; ?></p>
        </div>
    <?php } ?>

    <form action='options.php' method='post'>

        <h2>Configurazione Plugin WooCompany015</h2>

        <?php
        settings_fields( 'woocompanyPlugin' );
        do_settings_sections( 'woocompanyPlugin' );
        submit_button('Salva configurazione');
        ?>

    </form>


    <form action="" method='post'>
        <input type="hidden" name="action" value="woocompany_auth">
        <p><b>Premi il pulsante per attivare la configurazione</b> (&#9888; <i>Prima di attivare la configurazione devi salvarla</i>)</p>
        <?php submit_button( 'Attiva configurazione' ); ?><br>

        <?php
        $url = esc_url( add_query_arg(
      		'page',
      		'woocompany-menu3',
      		get_admin_url() . 'admin.php'
      	) );
      	$settings_link = "<a href='$url'>" . __( 'Help' ) . '</a>';
        ?>
        <p>guida alla configurazione - <?php echo $settings_link;?></p>


    </form>

    <?php
}


function woocompany_logs_page(  ) {
    $eventi=Array();
    if(file_exists(WOOCOMPANY_COMPANY015_LOGFILE)){
        $logs=file_get_contents(WOOCOMPANY_COMPANY015_LOGFILE);
        $eventi=explode("\n",$logs);
        $eventi=array_reverse($eventi);
    }

    ?>
    <h2>Registro Eventi</h2>
    <table style="border:1px solid; border-collapse: collapse;">
    <tr style="background-color:#98b8ee;height:50px;"><th>Data/Ora</th>
        <th>Evento</th>
        <th>ID Ordine</th>
        <th>Risposta</th>
    </tr>
    <?php

    foreach($eventi as $evento){
        $cells=explode(";",$evento);
        $resp="";
        if(isset($cells[3])){
           $response= json_decode($cells[3]);
           if(isset($response->coderesult)){
               $coderesult=$response->coderesult;
               if($coderesult=="200") $resp="Documento trasferito con successo";
               elseif($coderesult=="705") $resp="Chiave COMPANY015 non attiva";
               else $resp="Errore nel trasferimento del documento(codice $coderesult)";

           }else{
               $resp=$cells[3];
           }
        }
        ?>
       <tr style="border:1px solid">
           <td style="border:1px solid"><?php if(isset($cells[0])) echo($cells[0]); ?></td>
           <td style="border:1px solid"><?php if(isset($cells[1])) echo($cells[1]); ?></td>
           <td style="border:1px solid"><?php if(isset($cells[2])) echo($cells[2]); ?></td>
           <td style="border:1px solid"><?php echo $resp; ?></td>

       </tr>
        <?php


    }
   ?>
    </table>
    <?php

}
function woocompany_help_page(  ) {
    ?>
    <?php
    $txthelp="<br><br>Per poter utilizzare il plugin WooCompany015 è necessario avere una licenza attiva di COMPANY015. (<a target='_blank' href='https://www.company015.it'>www.company015.it</a>)<br>".
    "Il plugin consente di creare gli ordini o le fatture, automaticamente dopo la ricezione degli ordini effettuati sul sito ecommerce gestito con WooCommerce.<br><br>".

    "<h2>Attivazione del plugin</h2>".
    "Per attivare e configurare il plugin, occore effettuare le seguenti operazioni :<br>".
    "- in COMPANY015 dal menu <span style='color:#1053c1;font-weight:bold;'>Integrazioni Web - WooCommerce</span>, attivare l'integrazione come indicato nella funzione<br>".
    "- dal menu di gestione del plugin WooCompany015 accedere alla voce <span style='color:#1053c1;font-weight:bold;'>Configurazione</span><br><br>".

    "<h2>Configurazione WooCompany015</h2>".
    "<b>WooCompany015</b><br>".
    "- <b>chiave company015</b>: la <span style='color:#1053c1;font-weight:bold;'>chiave di attivazione</span> generata in COMPANY015 dalla funzione <span style='color:#1053c1;font-weight:bold;'>WooCommerce, del menu Integrazioni Web</span><br>".
    "- <b>codice account</b>: il <span style='color:#1053c1;font-weight:bold;'>codice account</span> presente in COMPANY015 nel <span style='color:#1053c1;font-weight:bold;'>menù account</span><br>".
    "- <b>documento da creare</b>: scegliere il documento da generare automaticamente in COMPANY015 : l'ordine o la fattura <br>".
    "- <b>sito web</b>: l'indirizzo del tuo sito e-commerce, nel formato (https://nomedelsito.it/)<br><br>".

    "<b>WooCommerce</b><br>".
    "- <b>chiave cliente</b>  : il valore assegnato da WooCommerce nel menu <span style='color:#1053c1;font-weight:bold;'>WooCommerce - Impostazioni - Avanzate - API REST - Consumer Key</span><br>".
    "- <b>utente nascosto</b> : il valore assegnato da WooCommerce nel menu <span style='color:#1053c1;font-weight:bold;'>WooCommerce - Impostazioni - Avanzate - API REST - Utente</span><br>".
    "nel caso non è ancora presente una chiave API REST, fare click sul tasto \"Aggiungi Chiave\", per crearne una, e copiare i valori Consumer Key ed Utente<br><br>".

    "<b>Salva Configurazione</b><br>".
    "Dopo aver inserito i dati, fare click sul tasto <b>Salva Configurazione</b>.<br><br>".

    "<b>Attiva Configurazione</b><br>".
    "Per <b>attivare</b> il plugin, fare click sul tasto <b>Attiva Configurazione</b>, in alto viene evidenziato il messaggio se l'operazione è andata a buon fine.<br>".
    "Se si riceve il messaggio <b>\"Errore: chiave non abilitata\"</b>, vuol dire che non c'è corrispondenza tra il valore \"chiave company015\" ed il valore \"chiave di attivazione\",".
    "della funzione woocommerce in COMPANY015.<br>".
    "Prima di attivare la configurazione, usare il tasto Salva Configurazione.".

    "<h3> Registro Eventi </h3>".
    "Il registro mostra le operazioni effettuate dal plugin quando un ordine viene completato sul sito ecommerce.<br>".
    "Nel registro sono riportati :<br>".
    "- <b>data/ora</b> : data ed ora dell'operazione<br>".
    "- <b>evento</b> : indica il tipo di operazione<br>".
    "- <b>ID Ordine</b>: numero ordine generato in woocommerce<br>".
    "- <b>Risposta</b>: esito del trasferimento del documento su COMPANY015.<br><br>".

    "Per ulteriori informazioni consulta il sito ufficiale a questo indirizzo:<br>".
    "<a target='_blank' href='https://www.company015.it/woocommerce/'>visita il sito del plugin</a>";

    echo $txthelp;
}

add_action( 'woocommerce_checkout_order_processed', 'woocompany_new_order'  );
add_action( 'woocommerce_new_order', 'woocompany_new_order_admin' );

function woocompany_new_order( $order_id ) {
    $action=current_filter();

    //Chiamata WooCompany
    $response=woocompany_postorder($order_id,"add");

    $line="\n".date("d-m-Y h:i:s").";Nuovo;$order_id;".json_encode($response);
    file_put_contents(WOOCOMPANY_COMPANY015_LOGFILE,$line,FILE_APPEND);

};
function woocompany_new_order_admin( $order_id ) {
    if ( ! is_admin()) { return; }
    $action=current_filter();

    //Chiamata WooCompany
    $response=woocompany_postorder($order_id,"add");

    $line="\n".date("d-m-Y h:i:s").";Nuovo;$order_id;".json_encode($response);
    file_put_contents(WOOCOMPANY_COMPANY015_LOGFILE,$line,FILE_APPEND);

};



//ORDINE MODIFICATO
add_action( 'woocommerce_process_shop_order_meta', 'woocompany_update_order', 10, 3  );

function woocompany_update_order($post_id, $post ) {

};


function woocompany_wauthcheck(){
    $options = get_option( 'woocompany_settings' );
    $kapi=$options['woocompany_kapi'];
    $module=$options['woocompany_module'];
    $website=$options['woocompany_website'];
    $woosecretkey=$options['woocompany_woosecretkey'];
    $woosecretuser=$options['woocompany_woosecretuser'];
    $seldoc=$options['woocompany_seldoc'];

    $endpoint=WOOCOMPANY_COMPANY015_ENDPOINT."/api/woo/wauthcheck.php";

    $bodyjson=array(
      'website' => $website,
      'woosecretkey' =>$woosecretkey,
      'woosecretuser' =>$woosecretuser,
      'seldoc' =>$seldoc
    );

    $body = wp_json_encode( $bodyjson );
    $jsonData = array(
      'method'      => 'POST',
      'headers'  => 'Content-Type: application/json',
      'body'        => $body
    );
    $urlpost=$endpoint."?module=$module&kapi=$kapi";
    $responsebody=wp_remote_post($urlpost, $jsonData);

    if ( is_wp_error( $responsebody ) ) {
        $error_message = $responsebody->get_error_message();
        $ret="{\"result\": \"0\",\"coderesult\": \"Error #:\" . $error_message\"}";
    } else {
        $ret= $responsebody['body'];
    }

    return json_decode($ret);
}


function woocompany_postorder($orderid,$operation){
    $options = get_option( 'woocompany_settings' );
    $kapi=$options['woocompany_kapi'];
    $module=$options['woocompany_module'];
    $website=$options['woocompany_website'];
    $woosecretkey=$options['woocompany_woosecretkey'];
    $woosecretuser=$options['woocompany_woosecretuser'];
    $seldoc=$options['woocompany_seldoc'];


    $endpoint=WOOCOMPANY_COMPANY015_ENDPOINT."/api/woo/postorder.php";

    $bodyjson=array(
      'orderid' => $orderid,
      'operation' =>$operation
    );

    $body = wp_json_encode( $bodyjson );
    $jsonData = array(
      'method'      => 'POST',
      'headers'  => 'Content-Type: application/json',
      'body'        => $body
    );
    $urlpost=$endpoint."?module=$module&kapi=$kapi";
    $responsebody=wp_remote_post($urlpost, $jsonData);

    if ( is_wp_error( $responsebody ) ) {
        $error_message = $responsebody->get_error_message();
        $ret="{\"result\": \"0\",\"coderesult\": \"Error #:\" . $error_message\"}";
    } else {
        $ret= $responsebody['body'];
    }

    return json_decode($ret);
}
