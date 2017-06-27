<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_conecta_plugin.php");
require_once modification("libs/db_sessoes.php");
require_once modification("dbforms/db_funcoes.php");
require_once(modification("dbforms/verticalTab.widget.php"));

require_once(modification("model/orcamento/ProgramacaoFinanceira/ProgramacaoFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceiraValor.model.php"));

$oGet = db_utils::postMemory($_GET);

$aMeses = array();
for ($iMes = 1; $iMes <= 12; $iMes++) {
  $aMeses[$iMes] = DBDate::getMesExtenso($iMes);
}

$oData = new DBDate(date('d/m/Y', db_getsession('DB_datausu')));
$iMes  = $oData->getMes();
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

  <link href="estilos.css" rel="stylesheet" type="text/css">
  <link href="estilos/grid.style.css" rel="stylesheet" type="text/css">
  <link href="estilos/tab.style.css" rel="stylesheet" type="text/css">

  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>

  <style>
    .tabela-valores {
      padding-top: 5px;
    }

    .tabela-valores tr td {
      padding: 5px;
    }

    .valores {
      background-color:#FFFFFF;
      outline: 1px solid #ccc;
      text-align: right;
      width: 100px;
    }
  </style>
</head>
<body>
<div class="subcontainer">

  <p class="bold" style="text-align: left;">
    Mês:<?php db_select("mes", $aMeses, true, 1, "onchange='buscarExecucaoMensal({$oGet->codigo});'") ?>
  </p>
  <fieldset>
    <legend class="bold">Dados da Execução no Mês</legend>

    <table class="tabela-valores">
      <tr>
        <td class="bold">Previsão:</td>
        <td nowrap id="previsao" class="valores"></td>
        <td class="bold">Empenhado:</td>
        <td nowrap id="empenhado" class="valores"></td>
        <td class="bold">Saldo a Empenhar no mês anterior:</td>
        <td nowrap id="saldo_empenhar_anterior" class="valores"></td>
      </tr>
      <tr>
        <td class="bold">Crédito:</td>
        <td nowrap id="credito" class="valores"></td>
        <td class="bold">Liquidado:</td>
        <td nowrap id="liquidado" class="valores"></td>
        <td class="bold">Saldo a Repassar do mês anterior:</td>
        <td nowrap id="saldo_repassar_anterior" class="valores"></td>
      </tr>
      <tr>
        <td class="bold">Redução:</td>
        <td nowrap id="reducao" class="valores"></td>
        <td class="bold">Pago:</td>
        <td nowrap id="pago" class="valores"></td>
        <td class="bold">Saldo a Pagar do mês anterior:</td>
        <td nowrap id="saldo_pagar_anterior" class="valores"></td>
      </tr>
      <tr>
        <td class="bold">Saldo Reservado:</td>
        <td nowrap id="saldo_reservado" class="valores"></td>
        <td class="bold">Repassado:</td>
        <td nowrap id="repassado" class="valores"></td>
      </tr>
    </table>
  </fieldset>
  <fieldset>
    <table class="tabela-valores">
      <tr>
        <td class="bold">Disponível para empenho:</td>
        <td nowrap id="disponivel_empenho" class="valores"></td>
        <td class="bold">Disponível para repasse financeiro:</td>
        <td nowrap id="disponivel_repasse" class="valores"></td>
        <td class="bold">Disponível para pagamento:</td>
        <td nowrap id="disponivel_pagamento" class="valores"></td>
      </tr>
    </table>
  </fieldset>
</div>
</body>
<script>

  var sURL = "orc4_programacaofinanceira.RPC.php";
  $('mes').value = <?= $iMes ?>;

  function buscarExecucaoMensal(iCodigoFichaFinanceira) {

    if (iCodigoFichaFinanceira.length == 0) {
      return alert("A Ficha Financeira não foi informada.");
    }

    if ($('mes').value.length == 0) {
      return alert("O mês não foi informado.");
    }

    oParametros = {
      exec   : 'getSaldosFichaMes',
      codigo : iCodigoFichaFinanceira,
      mes    : $('mes').value
    };

    new AjaxRequest(sURL, oParametros, function(oRetorno, lErro) {

      if (lErro) {
        return alert(oRetorno.mensagem.urlDecode());
      }

      $('previsao').innerHTML                = js_formatar(oRetorno.previsao, 'f');
      $('credito').innerHTML                 = js_formatar(oRetorno.credito, 'f');
      $('reducao').innerHTML                 = js_formatar(oRetorno.reducao, 'f');
      $('saldo_reservado').innerHTML         = js_formatar(oRetorno.saldo_reservado, 'f');
      $('saldo_empenhar_anterior').innerHTML = js_formatar(oRetorno.saldo_empenhar_anterior, 'f');
      $('empenhado').innerHTML               = js_formatar(oRetorno.empenhado, 'f');
      $('liquidado').innerHTML               = js_formatar(oRetorno.liquidado, 'f');
      $('repassado').innerHTML               = js_formatar(oRetorno.repassado, 'f');
      $('saldo_repassar_anterior').innerHTML = js_formatar(oRetorno.saldo_repassar_anterior, 'f');
      $('pago').innerHTML                    = js_formatar(oRetorno.pago, 'f');
      $('saldo_pagar_anterior').innerHTML    = js_formatar(oRetorno.saldo_pagar_anterior, 'f');
      $('disponivel_empenho').innerHTML      = js_formatar(oRetorno.disponivel_empenho, 'f');
      $('disponivel_repasse').innerHTML      = js_formatar(oRetorno.disponivel_repasse, 'f');
      $('disponivel_pagamento').innerHTML    = js_formatar(oRetorno.disponivel_pagamento, 'f');

    }).setMessage("Aguarde, carregando execução mensal.").execute();
  }

  buscarExecucaoMensal(<?= $oGet->codigo ?>);
</script>
</html>
