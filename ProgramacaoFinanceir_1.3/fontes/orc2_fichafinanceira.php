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
    .valores {background-color:#FFFFFF}
  </style>
</head>
<body>
<div class="field-size-max">

  <fieldset>
    <legend class="bold">Dados da Ficha Financeira</legend>

    <table>
      <tr>
        <td nowrap class="bold">Código:</td>
        <td nowrap id="codigo"class="valores field-size2"></td>
        <td nowrap id="descricao" class="valores" colspan="2" ></td>
      </tr>
      <tr>
        <td nowrap class="bold">Órgão:</td>
        <td nowrap id="orgao" class="valores"></td>
        <td nowrap id="orgao_descricao" class="valores" colspan="2"></td>
      </tr>
      <tr>
        <td nowrap class="bold">Unidade:</td>
        <td nowrap id="unidade" class="valores"></td>
        <td nowrap id="unidade_descricao" class="valores" colspan="2"></td>
      </tr>
      <tr>
        <td nowrap class="bold">Recurso:</td>
        <td nowrap id="recurso" class="valores"></td>
        <td nowrap id="recurso_descricao" class="valores" colspan="2"></td>
      </tr>
      <tr>
        <td nowrap class="bold">Anexo:</td>
        <td nowrap id="anexo" class="valores"></td>
        <td nowrap id="anexo_descricao" class="valores" colspan="2"></td>
      </tr>
      <tr>
        <td nowrap class="bold">Valor Orçado:</td>
        <td nowrap id="valor_orcado" class="valores"></td>
        <td nowrap class="bold field-size2">Valor Total:</td>
        <td nowrap id="valor_total" class="valores"></td>
      </tr>
      <tr>
        <td nowrap class="bold">Valor Indisponível:</td>
        <td nowrap id="valor_indisponivel" class="valores"></td>
        <td nowrap class="bold field-size2">Valor a Programar:</td>
        <td nowrap id="valor_programar" class="valores"></td>
      </tr>
    </table>
  </fieldset>
  <fieldset style='padding-left:0px'><legend><b>Detalhamento</b></legend>
    <?

    $oTabDetalhes = new verticalTab("detalhesficha", 300);
    $oTabDetalhes->add("fichaexecucaomensal", "Execução Mensal", "orc2_fichafinanceiradadosexecucao.php?codigo={$oGet->codigo}");
    $oTabDetalhes->add("fichadetalhemensal", "Detalhamento Mensal", "orc2_fichafinanceiradadosmensais.php?codigo={$oGet->codigo}");
    $oTabDetalhes->show();
    ?>
  </fieldset>
</div>
</body>
<script>

  var sURL = "orc4_programacaofinanceira.RPC.php";

  carregarFichaFinanceira(<?= $oGet->codigo ?>);

  function carregarFichaFinanceira(iCodigoFichaFinanceira) {

    if (iCodigoFichaFinanceira.length == 0) {
      return alert("A Ficha Financeira não foi informada.");
    }

    oParametros = {
      exec   : 'getDetalhesFicha',
      codigo : iCodigoFichaFinanceira
    };

    new AjaxRequest(sURL, oParametros, function(oRetorno, lErro) {

      if (lErro) {
        return alert(oRetorno.mensagem.urlDecode());
      }

      $('codigo').innerHTML            = oRetorno.codigo;
      $('descricao').innerHTML         = oRetorno.descricao.urlDecode();
      $('orgao').innerHTML             = oRetorno.orgao;
      $('orgao_descricao').innerHTML   = oRetorno.orgao_descricao.urlDecode();
      $('unidade').innerHTML           = oRetorno.unidade;
      $('unidade_descricao').innerHTML = oRetorno.unidade_descricao.urlDecode();
      $('recurso').innerHTML           = oRetorno.recurso;
      $('recurso_descricao').innerHTML = oRetorno.recurso_descricao.urlDecode();
      $('anexo').innerHTML             = oRetorno.anexo;
      $('anexo_descricao').innerHTML   = oRetorno.anexo_descricao.urlDecode();
      $('valor_orcado').innerHTML      = js_formatar(oRetorno.valor_orcado, 'f');
      $('valor_total').innerHTML       = js_formatar(oRetorno.valor_total, 'f');
      $('valor_programar').innerHTML    = js_formatar(oRetorno.valor_programar, 'f');
      $('valor_indisponivel').innerHTML = js_formatar(oRetorno.valor_indisponivel, 'f');

    }).setMessage("Aguarde, carregando Ficha Financeira.").execute();
  }
</script>
</html>
