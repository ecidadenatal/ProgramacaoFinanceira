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

$oDaoRI = new cl_resolucaointeradministrativa();
parse_str($_SERVER["QUERY_STRING"]);
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<body style="background-color: #CCCCCC">

<div class="container">
  <form name="form2">
    <fieldset style="width: 200px">
      <legend class="bold">Filtros</legend>

      <table>
        <tr>
          <td class="bold"><label for="codigo">Código:</label></td>
          <td>
            <?php
            db_input('funcao_js', 10, 1, true, 'hidden', 3);
            $Scodigo = "Código";
            db_input('codigo', 6, 1, true, 'text', 1);
            ?>
          </td>
        </tr>
        <tr>
          <td class="bold"><label for="numero">Número:</label></td>
          <td>
            <?php

            $Snumero = "Número";
            $Sano = "Ano";
            db_input('numero', 6, 1, true, 'text', 1);
            ?>
            /
            <?= db_input('ano', 4, 1, true, 'text', 1, "", "", "", "", 4) ?>
          </td>
        </tr>
        <tr>
          <td class="bold"><label for="data">Data:</label></td>
          <td>
            <?php

            $sDia = "";
            $sMes = "";
            $sAno = "";
            if (!empty($oGet->data)) {

              $aData = explode("/", $oGet->data);
              $sDia  = isset($aData[0]) ? $aData[0] : "";
              $sMes  = isset($aData[0]) ? $aData[1] : "";
              $sAno  = isset($aData[0]) ? $aData[2] : "";
            }

            $Sdata = "Data";
            db_inputdata('data', $sDia, $sMes, $sAno, true, 'text', 1);
            ?>
          </td>
        </tr>
      </table>
    </fieldset>

    <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar">
    <input name="limpar" type="reset" id="limpar" value="Limpar" >
    <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_resolucaointeradministrativa.hide();">
  </form>
</div>

<div class="container">

  <?php
  $sSituacao = "
    case when situacao = 1 then 'Em Elaboração'::varchar
         when situacao = 2 then 'Fechada'::varchar
         when situacao = 3 then 'Aprovada'::varchar
    end as dl_Situação";
  $aCampos = array(
    "sequencial as dl_Código",
    "(numero||'/'||ano)::varchar as dl_Número",
    "to_char(data, 'DD/MM/YYYY')::varchar as dl_Data",
    "objetivo as dl_Objetivo",
    $sSituacao,
  );
  $sCampos = implode(',', $aCampos);
  $sOrder  = "sequencial";
  $aWhere  = array();

  if (!empty($oGet->numero)) {

    $aWhere[] = " numero = " . $oGet->numero . " ";
  }

  if (!empty($oGet->ano)) {
    $aWhere[] = " ano = " . $oGet->ano . " ";
  }

  if (!empty($oGet->data)) {

    $sData = implode("-", array_reverse(explode("/", $oGet->data)));

    $aWhere[] = " data = '{$sData}' ";
  }

  if (!isset($pesquisa_chave)) {

    if (!empty($oGet->codigo)) {
      $aWhere[] = "sequencial = {$oGet->codigo}";
    }

    $sql = $oDaoRI->sql_query_file(null, $sCampos, $sOrder, implode(' and ', $aWhere));

    echo "<fieldset>";
    echo "  <legend class='bold'>Registros Encontrados</legend>";
    db_lovrot($sql, 15, "()", "", $oGet->funcao_js);
    echo "</fieldset>";

  } else{
    echo "<script>" . $oGet->funcao_js . "('', '', false);</script>";
  }
  ?>
</div>
</body>
</html>
