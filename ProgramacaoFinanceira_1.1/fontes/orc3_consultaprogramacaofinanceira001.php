<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBseller Servicos de Informatica
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
require_once ("libs/db_stdlib.php");
require_once ("libs/db_conecta_plugin.php");
require_once ("libs/db_sessoes.php");
require_once ("libs/db_utils.php");
require_once ("libs/db_app.utils.php");
require_once ("dbforms/db_funcoes.php");
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <script type="text/javascript" src="scripts/datagrid.widget.js"></script>
  <script type="text/javascript" src="scripts/classes/orcamento/FiltroFichaFinanceira.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">

  <fieldset style="width: 480px;">
    <legend class="bold">Consulta da Ficha Financeira</legend>
    <div id="ctnFiltroOrcamento">
    </div>
  </fieldset>
  <p align="center"><input type="button" id="btnPesquisar" value="Pesquisar" onclick="pesquisar()"/></p>
</div>

<div class="container">
  <fieldset style="width: 800px;">
    <legend class="bold">Registros Encontrados</legend>
    <div id="ctnRegistrosEncontrados"></div>
  </fieldset>
</div>
<?php
db_menu();
?>
</body>
</html>

<script>

  var oFiltro = new FiltroFichaFinanceira();
  oFiltro.show($('ctnFiltroOrcamento'));

  var oGrid = new DBGrid('oGrid');
  oGrid.nameInstance = 'oGrid';
  oGrid.setHeader(['Codigo', 'Descrição', 'Ação']);
  oGrid.setCellWidth(['0', '90%', '10%']);
  oGrid.setCellAlign(['left', 'left', 'center']);
  oGrid.aHeaders[0].lDisplayed = false;
  oGrid.setHeight(400);
  oGrid.show($('ctnRegistrosEncontrados'));

  function pesquisar() {

    if (oFiltro.getCodigoProgramacao() == "") {
      return alert("Campo Programação Financeira é de preenchimento obrigatório.");
    }

    oGrid.clearAll(true);
    var oParametro = {
      exec    : 'getFichas',
      codigo  : oFiltro.getCodigoProgramacao(),
      orgao   : oFiltro.getCodigoOrgao(),
      unidade : oFiltro.getCodigoUnidade(),
      recurso : oFiltro.getCodigoRecurso(),
      anexo   : oFiltro.getCodigoAnexo()
    };

    new AjaxRequest(
      'orc4_programacaofinanceira.RPC.php',
      oParametro,
      function (oRetorno, lErro) {

        if (lErro) {
          return alert(oRetorno.mensagem.urlDecode());
        }

        if (oRetorno.fichas.length == 0) {
          return alert("Não foram encontradas Fichas Financeiras para o filtro selecionado.");
        }
        
        oRetorno.fichas.each(
          function (oFicha, iIndiceGrid) {

            oGrid.addRow(
              [
                oFicha.codigo,
                oFicha.descricao.urlDecode(),
                "<input type='button' value='Consultar' onclick='consultarFichaFinanceira("+iIndiceGrid+")' />"
              ]
            );
          }
        );
        oGrid.renderRows();
      }
    ).setMessage('Aguarde, pesquisando fichas...').execute();
  }

  function consultarFichaFinanceira(iIndiceGrid) {

    var aDadosGrid       = oGrid.aRows[iIndiceGrid];
    var sArquivoConsulta = "orc2_fichafinanceira.php?codigo="+aDadosGrid.aCells[0].getValue();
    var sTituloJanela    = "Consulta de Ficha Financeira: "+aDadosGrid.aCells[1].getValue();
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_consultafichafinanceira', sArquivoConsulta, sTituloJanela, true);
  }

</script>
