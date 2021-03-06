<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/19/2016
 * Time: 8:57 PM
 */
class BSCS_model extends CI_Model{

    private $bscs_db = null;

    function __construct()
    {
        parent::__construct();

        try {

            $this->bscs_db = $this->load->database('BSCS', TRUE);

        }catch(Exception $ex){

        }
    }

    /*
     * Get MSISDN info from BSCS
     */
    function get_msisdn_info($msisdn)
    {

        if(strlen($msisdn) == 12){

            $msisdn = substr($msisdn, 3);

        }

        $response = -1; // $response remains this way if request fails

        $sql = "SELECT a.dn_num MSISDN, c.co_id CONTRACT_ID, cos.ch_status STATUT, cu.CSCUSTTYPE TYPE_CLIENT, 
                CASE cu.billcycle WHEN '05' THEN 'PREPAID' ELSE 'POSTPAID' END AS CUST_TYPE, cu.CUSTCODE NUM_COMPTE, CASE cc.CCLANGUAGE WHEN 3 
                THEN 'FR' ELSE 'EN' END AS LANGUE, cc.ccsex SEXE, cc.ccname STE, cc.cclname NOM, cc.ccfname
                PRENOM, cc.birthdate, cc.CSCOMPREGNO NUM_REGISTRE, cc.id_type, cc.passportno ID_PIECE, cc.ccjobdesc METIER, cc.cczip BP, cc.cccity VILLE, cccountry PAYS, cc.ccstate, cc.cctn, cc.cctn2,  cc.ccemail, 
                ic.customer_id AS ic_cst_id, ic.text04 AS birth_place, ic.text28 AS id_delivery_date, ic.text29 AS id_expiry_date, ic.text30 AS id_delivery_place 
                FROM ccontact_all cc INNER JOIN contract_all c ON cc.customer_id = c.customer_id 
                INNER JOIN curr_co_status cos ON cos.co_id = c.co_id INNER JOIN contr_services_cap b ON b.co_id = c.co_id 
                INNER JOIN directory_number a ON a.dn_id = b.dn_id INNER JOIN customer_all cu ON cu.customer_id = c.customer_id 
                LEFT JOIN info_cust_text ic ON ic.customer_id = c.customer_id 
                WHERE b.cs_deactiv_date IS NULL AND b.sncode=1 AND CCSEQ=1 AND a.dn_num='$msisdn'";

        if($this->bscs_db){

            $response = $this->bscs_db->query($sql)->row_array();

        }

        return $response;

    }

}
