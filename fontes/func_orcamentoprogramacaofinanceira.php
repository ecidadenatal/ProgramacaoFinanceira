<?
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

$clorcamentoprogramacaofinanceira = new cl_orcamentoprogramacaofinanceira();
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
    <fieldset style="width: 500px">
      <legend class="bold">Filtros</legend>

      <table>
        <tr>
          <td class="bold"><label for="codigo">Código:</label></td>
          <td>
            <?php
            db_input('funcao_js', 10, 1, true, 'hidden', 3);
            $Scodigo = "Código";
            db_input('codigo', 10, 1, true, 'text', 1);
            ?>
          </td>
        </tr>
        <tr>
          <td class="bold"><label for="descricao">Descrição:</label></td>
          <td>
            <?php
            $Sdescricao = "Descrição";
            db_input('descricao', 50, 3, true, 'text', 1);
            ?>
          </td>
        </tr>
      </table>
    </fieldset>

    <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar">
    <input name="limpar" type="reset" id="limpar" value="Limpar" >
    <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_orcamentoprogramacaofinanceira.hide();">
  </form>
</div>

<div class="container">


  <?php
  $aWhere = array(
    "instituicao = {$iInstituicaoSessao}"
  );

  if (!isset($pesquisa_chave)) {

    $aCampos = array(
      "sequencial as dl_Código",
      "cronograma as dl_Cronograma_de_Desembolso",
      "(instituicao ||' - '|| nomeinst)::varchar as dl_Instituição",
      "ano as dl_Ano",
      "descricao::varchar as dl_Descrição",
      "situacao as dl_Situação",
      "case when situacao = 1 then 'Em Elaboração'::varchar else case when situacao = 2 then 'Fechada'::varchar else 'Aprovada'::varchar end end as dl_Descrição_Situação"
    );

    $campos = implode(',',$aCampos);

    $sql = $clorcamentoprogramacaofinanceira->sql_query_instituicao($campos, implode(' and ', $aWhere));
    if (!empty($oGet->codigo)) {

      $aWhere[] = "sequencial = {$oGet->codigo}";
      $sql = $clorcamentoprogramacaofinanceira->sql_query_instituicao($campos, implode(' and ', $aWhere));

    } elseif (!empty($oGet->descricao)) {

      $aWhere[] = "descricao like '{$oGet->descricao}%'";
      $sql = $clorcamentoprogramacaofinanceira->sql_query_instituicao($campos, implode(' and ', $aWhere));
    }

    $repassa = array();


    echo "<fieldset>";
    echo "  <legend class='bold'>Registros Encontrados</legend>";
    db_lovrot($sql, 15, "()", "", $oGet->funcao_js, "", "NoMe", $repassa, true);
    echo "</fieldset>";

  } else {

    if (!empty($oGet->pesquisa_chave)) {

      $aWhere[] = "sequencial = {$oGet->pesquisa_chave}";
      $sSqlBuscaProgramacao  =$clorcamentoprogramacaofinanceira->sql_query_file(null, "*", null, implode(' and ', $aWhere));
      $rsProgramacao = $clorcamentoprogramacaofinanceira->sql_record($clorcamentoprogramacaofinanceira->sql_query_file(null, "*", null, implode(' and ', $aWhere)));
      if ($clorcamentoprogramacaofinanceira->numrows != 0) {

        $oStdProgramacao = db_utils::fieldsMemory($rsProgramacao, 0);
        echo "<script>".$oGet->funcao_js."('$oStdProgramacao->descricao',false);</script>";
      } else {
        echo "<script>".$oGet->funcao_js."('Chave(".$oGet->pesquisa_chave.") não Encontrado',true, '');</script>";
      }

    }else{
      echo "<script>".$oGet->funcao_js."('',false);</script>";
    }
  }
  ?>
</div>
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
  js_tabulacaoforms("form2","descricao",true,1,"descricao",true);
  (function() {
    var query = frameElement.getAttribute('name').replace('IF', ''), input = document.querySelector('input[value="Fechar"]');
    input.onclick = parent[query] ? parent[query].hide.bind(parent[query]) : input.onclick;
  })();
</script>
