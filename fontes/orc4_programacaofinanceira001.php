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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oGet = db_utils::postMemory($_GET);

?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <script type="text/javascript" src="scripts/widgets/DBAbas.widget.js"></script>
  <script type="text/javascript" src="scripts/datagrid.widget.js"></script>
  <script type="text/javascript" src="scripts/classes/orcamento/ViewFichaFinanceira.js"></script>
  <script type="text/javascript" src="scripts/classes/orcamento/FiltroFichaFinanceira.js"></script>

  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">

  <div id="ctnAbasProgramacaoFinanceira">

    <div id="ctnProgramacaoFinanceira">
      <fieldset style="width: 600px">
        <legend class="bold">Informações da Programação Financeira</legend>
        <table>
          <tr>
            <td class="bold">
              <label for="codigo">Código:</label>
            </td>
            <td>
              <?php db_input("codigo", 10, 1, true, "text", 3); ?>
            </td>
          </tr>
          <tr>
            <td class="bold">
              <label for="ano">Ano:</label>
            </td>
            <td>
              <?php
              $ano = db_getsession('DB_anousu');
              db_input("ano", 10, 1, true, "text", 3);
              ?>
            </td>
          </tr>
          <tr>
            <td class="bold">
              <label for="cronograma_desembolso_codigo" id="labelCronogramaCodigo">
                <?php db_ancora("Cronograma de Desembolso:", "pesquisaCronogramaDesembolso(true)", 1); ?>
              </label>
            </td>
            <td>
              <?php
              $Scronograma_desembolso_codigo = "Cronograma de Desembolso";
              db_input("cronograma_desembolso_codigo", 10, 1, true, "text", 1, "onchange='pesquisaCronogramaDesembolso(false)'");
              db_input("cronograma_desembolso_descricao", 41, 1, true, "text", 3);
              ?>
            </td>
          </tr>
          <tr>
            <td class="bold">
              <label for="descricao">Descrição:</label>
            </td>
            <td>
              <?php
              $Sdescricao = "Descrição";
              db_input("descricao", 55, 3, true, "text", 1);
              ?>
            </td>
          </tr>
        </table>
      </fieldset>
      <p style="text-align: center">
        <input type="button" id="btnSalvar" value="Salvar" onclick="salvar()" />
        <input type="button" id="btnExcluir" value="Excluir" onclick="excluir()" />
        <input type="button" id="btnPesquisar" value="Pesquisar" onclick="pesquisar()" />
      </p>
    </div>

    <div id="ctnDadosFicha" class="subcontainer">

      <div class="subcontainer">
        <fieldset>
          <legend>Filtros</legend>
          <div id="ctnFiltroProgramacaoFinanceira"></div>
        </fieldset>
      </div>

      <input type="button" onclick="getFichasFinanceiras()" value="Pesquisar">

      <fieldset style="width: 900px">
        <legend class="bold">Fichas da Programação Financeira</legend>
        <div id="ctnGridFichasProgramacaoFinanceira">
        </div>
      </fieldset>
    </div>

  </div>

</div>
<?php db_menu(); ?>
<script type="text/javascript">
  const OPCAO_INCLUSAO  = '1';
  const OPCAO_ALTERACAO = '2';
  const OPCAO_EXCLUSAO  = '3';
  var oGet = js_urlToObject();
  var oGrid = null;
  var oViewFicha = new ViewFichaFinanceira('oViewFicha');
  oViewFicha.setCallBackFunction(getFichasFinanceiras);

  var FiltroFichaFinanceira = new FiltroFichaFinanceira();
  FiltroFichaFinanceira.exibeProgramacaoFinanceira(false);
  FiltroFichaFinanceira.show($('ctnFiltroProgramacaoFinanceira'));

  /**
   * @todo
   * alterar o nome do arquivo
   */
  const ARQUIVO_RPC = 'orc4_programacaofinanceira.RPC.php';

  var oAbas = new DBAbas($('ctnAbasProgramacaoFinanceira'));
  oAbas.adicionarAba('Programação Financeira', $('ctnProgramacaoFinanceira'), true);
  var oAbaFichasFinanceiras = oAbas.adicionarAba('Fichas', $('ctnDadosFicha'));
  var oDivFichas = $('Fichas');
  var sFunctionFichaFinanceira = oDivFichas.onclick;

  /**
   * Funcao que verifica se existe uma programação financeira selecionada
   * @returns {boolean}
   */
  oDivFichas.onclick = function() {

    if ($F('codigo') == '' || oGet.opcao == OPCAO_EXCLUSAO) {
      return false;
    }

    sFunctionFichaFinanceira();

    if (!oGrid) {

      oGrid = new DBGrid('oGrid');
      oGrid.nameInstance = 'oGrid';
      oGrid.setHeader(['Descrição', 'Valor Total', 'Valor Orçado', 'Ação']);
      oGrid.setCellWidth(['50%', '15%', '15%', '10%']);
      oGrid.setCellAlign(['left', 'right', 'right', 'center']);
      oGrid.setHeight(400);
      oGrid.show($('ctnGridFichasProgramacaoFinanceira'));
    }

    getFichasFinanceiras();
  };

  /**
   *  Salva ou Altera a as informações de cadastro da Programação Financeira
   */
  function salvar() {

    if ($F('cronograma_desembolso_codigo') == "") {
      $('cronograma_desembolso_codigo').focus();
      return alert("Campo Cronograma de Desembolso é de preenchimento obrigatório.");
    }

    if ($F('descricao').trim() == "") {
      $('descricao').focus();
      return alert('Campo Descrição é de preenchimento obrigatório.');
    }

    var oParametros = {
      exec   : 'salvar',
      codigo : $F('codigo'),
      ano    : $F('ano'),
      cronograma_desembolso : $F('cronograma_desembolso_codigo'),
      descricao : encodeURIComponent(tagString($F('descricao')))
    };

    new AjaxRequest(
      ARQUIVO_RPC,
      oParametros,
      function (oRetorno, lErro) {

        if (lErro) {
          return alert(oRetorno.mensagem.urlDecode());
        }

        bloquearAncoraCronogramaPerspectiva();

        $('codigo').value = oRetorno.codigo;
        return alert(oRetorno.mensagem.urlDecode());
      }
    ).setMessage('Aguarde, salvando informações...').execute();
  }

  /**
   * Busca as fichas financeiras associadas a programação financeira selecionada preenchendo na grid da aba Fichas
   */
  function getFichasFinanceiras() {

    var oParametros = {
      exec : 'getFichas',
      codigo : $F('codigo'),
      orgao : FiltroFichaFinanceira.getCodigoOrgao(),
      unidade : FiltroFichaFinanceira.getCodigoUnidade(),
      recurso : FiltroFichaFinanceira.getCodigoRecurso(),
      anexo : FiltroFichaFinanceira.getCodigoAnexo()
    };

    new AjaxRequest(
      ARQUIVO_RPC,
      oParametros,
      function (oRetorno, lErro) {

        oGrid.clearAll(true);

        if (lErro) {
          return alert(oRetorno.mensagem.urlDecode());
        }

        if (oRetorno.fichas.length == 0) {
          return alert("Nenhum registro encontrado.");
        }

        oRetorno.fichas.each(
          function (oFicha) {

            var sButton = "<input type='button' value='Manutenção' onclick='abrirManutencao("+oFicha.codigo+")' />";
            oGrid.addRow(
              [
                oFicha.descricao.urlDecode(),
                js_formatar(oFicha.valor_total, 'f'),
                js_formatar(oFicha.valor_orcado, 'f'),
                sButton
              ]
            );
          }
        );
        oGrid.renderRows();
      }
    ).setMessage('Aguarde, carregando fichas...').execute();
  }

  function pesquisar() {

    var sArquivo = "func_orcamentoprogramacaofinanceira.php?funcao_js=parent.preencherProgramacao|0";
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_programacaofinanceira', sArquivo, 'Programação Financeira', true);
  }

  function preencherProgramacao(iCodigo) {

    $('codigo').value = iCodigo;
    db_iframe_programacaofinanceira.hide();
    carregarInformacoesProgramacaoFinanceira();
  }

  /**
   * Carrega as informaçõe da Programação Financeira selecionada
   */
  function carregarInformacoesProgramacaoFinanceira() {

    if ($F('codigo') == '') {
      return alert("Programação Financeira não selecionada.");
    }

    new AjaxRequest(
      ARQUIVO_RPC,
      {exec : 'getProgramacaoFinanceira', codigo : $F('codigo')},
      function (oRetorno, lErro) {

        if (lErro) {
          return alert(oRetorno.mensagem.urlDecode());
        }

        $('ano').value = oRetorno.ano;
        $('cronograma_desembolso_codigo').value = oRetorno.codigo_cronograma_desembolso;
        $('descricao').value = oRetorno.descricao.urlDecode();
        pesquisaCronogramaDesembolso(false);
      }
    ).setMessage('Aguarde, carregando informações....').execute();
  }

  /**
   * Abre o componente para manutenção da ficha financeira
   */
  function abrirManutencao (iCodigoFicha) {
    oViewFicha.show(iCodigoFicha);
  }

  /**
   * Exclui uma programação financeira
   * @returns {boolean}
   */
  function excluir() {

    if ($F('codigo') == '') {
      return alert('Selecione uma Programação Financeira para excluir.');
    }

    var sMensagem = "Confirma a exclusão da programação financeira?\n\nEste procedimento não poderá ser desfeito.";
    if (!confirm(sMensagem)) {
      return false;
    }

    new AjaxRequest(
      ARQUIVO_RPC,
      { exec : 'excluir', codigo : $F('codigo') },
      function (oRetorno, lErro) {

        alert(oRetorno.mensagem.urlDecode());
        if (!lErro) {

          $('codigo').value = '';
          $('ano').value    = '';
          $('cronograma_desembolso_codigo').value    = '';
          $('cronograma_desembolso_descricao').value = '';
          $('descricao').value = '';
          $('btnPesquisar').click();
        }
      }
    ).setMessage('Aguarde, excluindo programação financeira...').execute();
  }

  /**
   * Configura a rotina para utilização de acordo com a opção desejada
   */
  function iniciar() {

    var oButtonSalvar    = $('btnSalvar');
    var oButtonPesquisar = $('btnPesquisar');
    var oButtonExcluir   = $('btnExcluir');
    var oDescricao       = $('descricao');
    oDescricao.maxLength = 200;

    oButtonPesquisar.style.display = 'none';
    oButtonExcluir.style.display   = 'none';

    switch (oGet.opcao) {

      case OPCAO_ALTERACAO:

        bloquearAncoraCronogramaPerspectiva();
        oButtonPesquisar.style.display = '';
        oButtonPesquisar.click();

        break;

      case OPCAO_EXCLUSAO:

        oButtonPesquisar.style.display = '';
        oButtonExcluir.style.display   = '';
        oButtonSalvar.style.display    = 'none';
        oButtonPesquisar.click();

        oDescricao.style.backgroundColor = '#DEB887';
        oDescricao.style.textColor       = '#000000';
        oDescricao.readOnly = true;

        bloquearAncoraCronogramaPerspectiva();

        break;
    }
  }
  iniciar();

  /**
   * Bloqueio da âncora de pesquisa de cronograma
   */
  function bloquearAncoraCronogramaPerspectiva() {

    var oLabelCronograma = $('labelCronogramaCodigo');
    oLabelCronograma.innerHTML = 'Cronograma de Desembolso:';
    var oCodigoCronograma = $('cronograma_desembolso_codigo');
    oCodigoCronograma.style.backgroundColor = '#DEB887';
    oCodigoCronograma.style.textColor       = '#000000';
    oCodigoCronograma.readOnly = true;
  }


  var pesquisaCronogramaDesembolso = function (lMostra) {

    var sArquivo = "func_cronogramaperspectiva.php?funcao_js=parent.completaCronogramaDesembolso|0|2";

    if (!lMostra) {
      sArquivo = "func_cronogramaperspectiva.php?&pesquisa_chave="+$F('cronograma_desembolso_codigo')+"&funcao_js=parent.validaCronogramaDesembolso";
    }
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_cronogramaperspectiva', sArquivo,'Pesquisa de Cronograma de Desembolso', lMostra);
  };

  var completaCronogramaDesembolso = function (iCodigo, sDescricao) {

    $('cronograma_desembolso_codigo').value = iCodigo;
    $('cronograma_desembolso_descricao').value = sDescricao;
    db_iframe_cronogramaperspectiva.hide();
  };

  var validaCronogramaDesembolso = function (sDescricao, lErro) {

    $('cronograma_desembolso_descricao').value = sDescricao;
    if (lErro) {
      $('cronograma_desembolso_codigo').value = '';
    }
  };
</script>
</body>
</html>


