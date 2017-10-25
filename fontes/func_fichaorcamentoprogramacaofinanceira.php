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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oGet  = db_utils::postMemory($_GET);
$oPost = db_utils::postMemory($_POST);
$iInstituicaoSessao = db_getsession('DB_instit');

$oDaoFichaProgramacaoFinanceira = new cl_fichaorcamentoprogramacaofinanceira();
parse_str($_SERVER["QUERY_STRING"]);
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
</head>
<body style="background-color: #CCCCCC">

<div class="container">
  <form name="form2">
    <fieldset style="width: 500px">
      <legend class="bold">Filtros</legend>

      <table>
        <tr>
          <td><label><?= db_ancora("Órgão:", "buscarPorTipo(true, 1)", 1) ?></label></td>
          <td>
            <?php

            $Scodigo_orgao = "Órgão";
            db_input('funcao_js', 10, 1, true, 'hidden', 3);
            db_input("codigo_orgao", 10, 1, true, 'text', 1, "onChange=\"buscarPorTipo(false, 1);\"");
            db_input("descricao_orgao", 45, 1, true, 'text', 3);
            ?>
          </td>
        </tr>
        <tr>
          <td><label><? db_ancora("Unidade:", "buscarPorTipo(true, 2)", 1) ?></label></td>
          <td>
            <?php

            $Scodigo_unidade = "Unidade";
            db_input("codigo_unidade", 10, 1, true, 'text', 1, "onChange=\"buscarPorTipo(false, 2);\"");
            db_input("descricao_unidade", 45, 1, true, 'text', 3);
            ?>
          </td>
        </tr>
        <tr>
          <td><label><?= db_ancora("Recurso:", "buscarPorTipo(true, 3)", 1) ?></label></td>
          <td>
            <?php

            $Scodigo_recurso = "Recurso";
            db_input("codigo_recurso", 10, 1, true, 'text', 1, "onChange=\"buscarPorTipo(false, 3);\"");
            db_input("descricao_recurso", 45, 1, true, 'text', 3);
            ?>
          </td>
        </tr>
        <tr>
          <td><label><?= db_ancora("Anexo:", "buscarPorTipo(true, 4)", 1) ?></label></td>
          <td>
            <?php

            $Scodigo_anexo = "Anexo";
            db_input("codigo_anexo", 10, 1, true, 'text', 1, "onChange=\"buscarPorTipo(false, 4);\"");
            db_input("descricao_anexo", 45, 1, true, 'text', 3);
            ?>
          </td>
        </tr>
      </table>
    </fieldset>

    <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar">
    <input name="limpar" type="reset" id="limpar" value="Limpar" >
    <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_fichaorcamentoprogramacaofinanceira.hide();">
  </form>
</div>

<div class="container">


  <?php

  $sDescricaoFicha  = " (lpad(orgao, 2, 0) || '.' || lpad(unidade, 3, 0) || ' - ' || o15_descr || '/' ";
  $sDescricaoFicha .= " || o11_descricao)::varchar ";

  $sCampos = " fichaorcamentoprogramacaofinanceira.sequencial as codigo, {$sDescricaoFicha} as descricao";
  $sOrder = "fichaorcamentoprogramacaofinanceira.orgao, fichaorcamentoprogramacaofinanceira.unidade, fichaorcamentoprogramacaofinanceira.recurso, fichaorcamentoprogramacaofinanceira.anexo";

  if (!isset($pesquisa_chave)) {

    $sCampos  = " fichaorcamentoprogramacaofinanceira.sequencial as dl_Código, {$sDescricaoFicha} as dl_Descrição ";

    if (!empty($oGet->codigo_orgao)) {
      $aWhere[] = " orgao = {$oGet->codigo_orgao} ";
    }

    if (!empty($oGet->codigo_unidade)) {
      $aWhere[] = " unidade = {$oGet->codigo_unidade} ";
    }

    if (!empty($oGet->codigo_recurso)) {
      $aWhere[] = " recurso = {$oGet->codigo_recurso} ";
    }

    if (!empty($oGet->codigo_anexo)) {
      $aWhere[] = " anexo = {$oGet->codigo_anexo} ";
    }

    $iAno = db_getsession('DB_anousu');
    $aWhere[] = " orcamentoprogramacaofinanceira.ano = {$iAno} ";


    $sql = $oDaoFichaProgramacaoFinanceira->sql_query(null, $sCampos, $sOrder, implode(' and ', $aWhere));

    echo "<fieldset>";
    echo "  <legend class='bold'>Registros Encontrados</legend>";
    db_lovrot($sql, 15, "()", "", $oGet->funcao_js);
    echo "</fieldset>";

  } else {

    if (!empty($oGet->pesquisa_chave)) {

      $aWhere[] = " fichaorcamentoprogramacaofinanceira.sequencial = {$oGet->pesquisa_chave} ";

      $sSqlBuscaFichaProgramacao = $oDaoFichaProgramacaoFinanceira->sql_query(null, $sCampos, $sOrder, implode(' and ', $aWhere));
      $rsFichaProgramacao        = $oDaoFichaProgramacaoFinanceira->sql_record($sSqlBuscaFichaProgramacao);
      if ($rsFichaProgramacao != false && $oDaoFichaProgramacaoFinanceira->numrows != 0) {

        $oStdFichaProgramacao = db_utils::fieldsMemory($rsFichaProgramacao, 0);
        echo "<script>".$oGet->funcao_js."($oStdFichaProgramacao->codigo, '$oStdFichaProgramacao->descricao', false);</script>";
      } else {
        echo "<script>".$oGet->funcao_js."('', 'Chave(".$oGet->pesquisa_chave.") não Encontrado', true);</script>";
      }

    } else{
      echo "<script>".$oGet->funcao_js."('', '', false);</script>";
    }
  }
  ?>
</div>
<script type="text/javascript">

  const TIPO_ORGAO   = 1;
  const TIPO_UNIDADE = 2;
  const TIPO_RECURSO = 3;
  const TIPO_ANEXO   = 4;

  function buscarPorTipo(lMostrar, iTipo) {

    var sNome   = "";
    var sCampos = "";
    var sFonte  = "";
    var sIframe = "";
    var sPesquisa = "";
    var sNomeCampo = "";
    switch (iTipo) {

      case TIPO_ORGAO:

        sNome      = "Órgão";
        sNomeCampo = 'orgao';
        sCampos    = "|0|2";
        sFonte     = "func_orcorgao.php";
        sIframe    = "db_iframe_orcorgao";
        break;

      case TIPO_UNIDADE:

        sNome      = "Unidade";
        sNomeCampo = "unidade";
        sCampos    = "|2|4";
        sFonte     = "func_orcunidade.php";
        sIframe    = "db_iframe_orcunidade";
        break;

      case TIPO_RECURSO:

        sNome      = "Recurso";
        sNomeCampo = "recurso";
        sCampos    = "|0|1";
        sFonte     = "func_orctiporec.php";
        sIframe    = "db_iframe_orctiporec";
        break;

      case TIPO_ANEXO:

        sNome      = "Anexo";
        sNomeCampo = "anexo";
        sCampos    = "|0|2";
        sFonte     = "func_ppasubtitulolocalizadorgasto.php";
        sIframe    = "db_iframe_ppasubtitulolocalizadorgasto";
        break;

      default:
        return;
    }

    if (!lMostrar) {

      sCampos = "";
      if ($('codigo_' + sNomeCampo).value == "") {

        $('descricao_' + sNomeCampo).value = "";
        return;
      }
      sPesquisa   = "pesquisa_chave=" + $('codigo_' + sNomeCampo).value+"&";
      sNomeCampo += "codigo";
    }

    var sArquivo = sFonte + "?"+sPesquisa+"funcao_js=parent.preencher"+sNomeCampo+sCampos;
    js_OpenJanelaIframe('CurrentWindow.corpo.IFdb_iframe_fichaorcamentoprogramacaofinanceira', sIframe, sArquivo, 'Pesquisa ' + sNome, lMostrar);
  }

  function preencherorgao(iCodigo, sDescricao, lErro) {
    preencherPorTipo(iCodigo, sDescricao, lErro, TIPO_ORGAO);
  }

  function preencherorgaocodigo(sDescricao, lErro) {
    preencherPorTipo($('codigo_orgao').value, sDescricao, lErro, TIPO_ORGAO);
  }

  function preencherunidade(iCodigo, sDescricao, lErro) {
    preencherPorTipo(iCodigo, sDescricao, lErro, TIPO_UNIDADE);
  }

  function preencherunidadecodigo(sDescricao, lErro) {
    preencherPorTipo($('codigo_unidade').value, sDescricao, lErro, TIPO_UNIDADE);
  }

  function preencherrecurso(iCodigo, sDescricao, lErro) {
    preencherPorTipo(iCodigo, sDescricao, lErro, TIPO_RECURSO);
  }

  function preencherrecursocodigo(sDescricao, lErro) {
    preencherPorTipo($('codigo_recurso').value, sDescricao, lErro, TIPO_RECURSO);
  }

  function preencheranexo(iCodigo, sDescricao, lErro) {
    preencherPorTipo(iCodigo, sDescricao, lErro, TIPO_ANEXO);
  }

  function preencheranexocodigo(sDescricao, lErro) {
    preencherPorTipo($('codigo_anexo').value, sDescricao, lErro, TIPO_ANEXO);
  }

  function preencherPorTipo(iCodigo, sDescricao, lErro, iTipo) {

    if (lErro) {
      iCodigo = '';
    }

    var oIframe    = null;
    var sNomeCampo = "";

    switch (iTipo) {

      case TIPO_ORGAO:

        sNomeCampo = 'orgao';
        oIframe    = db_iframe_orcorgao;
        break;

      case TIPO_UNIDADE:

        sNomeCampo = "unidade";
        oIframe    = db_iframe_orcunidade;
        break;

      case TIPO_RECURSO:

        sNomeCampo = "recurso";
        oIframe    = db_iframe_orctiporec;
        break;

      case TIPO_ANEXO:

        sNomeCampo = "anexo";
        oIframe    = db_iframe_ppasubtitulolocalizadorgasto;
        break;

      default:
        return;
    }

    $('codigo_' + sNomeCampo).value    = iCodigo;
    $('descricao_' + sNomeCampo).value = sDescricao;
    oIframe.hide();
  }

  function preencherTodos() {

    buscarPorTipo(false, TIPO_ORGAO);
    buscarPorTipo(false, TIPO_UNIDADE);
    buscarPorTipo(false, TIPO_RECURSO);
    buscarPorTipo(false, TIPO_ANEXO);
  }

  preencherTodos();
</script>
</body>
</html>
<?
if(!isset($pesquisa_chave)){
  ?>
  <script>
  </script>
  <?
}
?>

<script type="text/javascript">
(function() {
  var query = frameElement.getAttribute('name').replace('IF', ''), input = document.querySelector('input[value="Fechar"]');
  input.onclick = parent[query] ? parent[query].hide.bind(parent[query]) : input.onclick;
})();
</script>
