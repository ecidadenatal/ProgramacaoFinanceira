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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

require_once(modification("model/orcamento/ProgramacaoFinanceira/ResolucaoInteradministrativa.model.php"));

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

  <link href="estilos.css" rel="stylesheet" type="text/css">
  <style type="text/css">
    .fieldset-abas {
      width: 700px;;
    }
    .field-size-fichas {
      width: 100px;
    }
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
      width: 86px;
    }
  </style>
</head>
<body>
<div class="container">

  <div id="ctnAbasResolucaoFinanceira">

    <div id="ctnResolucaoFinanceira" class="subcontainer">

      <fieldset class="fieldset-abas">
        <legend class="bold">Dados da Resolução Inter-Administrativa</legend>

        <table>
          <tr>
            <td nowrap style="width: 85px;"><label class="bold" for="codigo">Código:</label></td>
            <td><?= db_input("codigo", 10, 0, true, "text", 3, "class='field-size2'"); ?></td>
          </tr>
          <tr>
            <td><label class="bold" for="numero">Número da RI:</label></td>
            <td><?= db_input("numero", 10, 0, true, "text", 3, "class='field-size2'"); ?></td>
          </tr>
          <tr>
            <td><label class="bold" for="processo">Processo:</label></td>
            <td><?= db_input("processo", 10, 0, true, 'text', $oGet->opcao, "class='field-size2'", "", "", "", 10); ?></td>
          </tr>
          <tr>
            <td><label class="bold" for="data">Data:</label></td>
            <td><?= db_inputdata("data", "", "", "", true, 'text', $oGet->opcao, "class='field-size2'"); ?></td>
          </tr>
          <tr>
            <td colspan="2">
              <fieldset>
                <legend>Objetivo</legend>
                <?= db_textarea("objetivo", 5, 90, 0, true, 'text', $oGet->opcao); ?>
              </fieldset>
            </td>
          </tr>
        </table>
      </fieldset>
      <input id="btnRISalvar"    type="button" value="Salvar" />
      <input id="btnRIExcluir"   type="button" value="Excluir" />
      <input id="btnRIPesquisar" type="button" value="Pesquisar" />
    </div>

    <div id="ctnFichaResolucaoFinanceira" class="subcontainer">

      <fieldset class="fieldset-abas">
        <legend>Ficha</legend>

        <table>
          <tr>
            <td>
              <label class="bold" for="codigo_ficha">
                <?= db_ancora("Ficha:", "buscarFicha(true);", 1); ?>
              </label>
            </td>
            <?php $Sficha_codigo = 'Ficha' ?>
            <td><?= db_input("ficha_codigo", 10, 1, true, 'text', 1, "class='field-size-fichas'"); db_input("ficha_descricao", 70, 0, true); ?></td>
          </tr>
          <tr>
            <td><label class="bold" for="natureza">Natureza:</label></td>
            <td>
              <?php

              $aNaturezas    = array();
              $aNaturezas[0] = "Selecione";
              $aNaturezas = array_merge($aNaturezas, ResolucaoInteradministrativa::getNaturezas());
              echo db_select("natureza", $aNaturezas, true, 1, "class='field-size-fichas'");
              ?>
            </td>
          </tr>
          <tr>
            <td><label class="bold" for="valor">Valor:</label></td>
            <?php $Svalor = 'Valor' ?>
            <td><?= db_input("valor", 10, 4, true, 'text', 1, "class='field-size-fichas'", "", "", "" , 15); ?></td>
          </tr>
          <tr id="linha_mes" style="display: table-row">
            <td><label class="bold" for="mes">Mês:</label></td>
            <td>
              <?php

              $aMeses    = array();
              $aMeses[0] = "Selecione";
              for ($iMes = DBDate::JANEIRO; $iMes <= DBDate::DEZEMBRO; $iMes++) {
                $aMeses[$iMes] = DBDate::getMesExtenso($iMes);
              }
              echo db_select("mes", $aMeses, true, 1, "class='field-size-fichas'");
              ?>
            </td>
          </tr>
        </table>
      </fieldset>

      <input id="btnFichasLancar" type="button" value="Lançar" />

      <fieldset class="fieldset-abas">
        <legend>Fichas Lançadas</legend>

        <div id="ctnGridFichasRI"></div>
      </fieldset>
      <fieldset class="fieldset-abas">
        <legend>Totalizadores da RI </legend>
        <table class="tabela-valores">
          <tr>
            <td class="bold">Crédito:</td>
            <td id="total_credito" class="valores">0,00</td>
            <td class="bold">Redução:</td>
            <td id="total_reducao" class="valores">0,00</td>
            <td class="bold">Indisponível:</td>
            <td id="total_indisponivel" class="valores">0,00</td>
            <td class="bold">Outras Fontes:</td>
            <td id="total_outras_fontes" class="valores">0,00</td>
          </tr>
        </table>
      </fieldset>
      <input id="btnFichasSalvar" type="button" value="Salvar">
    </div>
  </div>
</div>
<?php db_menu(); ?>
<script type="text/javascript">

  const RPC = "orc4_resolucaointeradministrativa.RPC.php";

  /**
   * Constantes para as opções da tela.
   * @type {string}
   */
  const OPCAO_INCLUSAO  = '1';
  const OPCAO_ALTERACAO = '2';
  const OPCAO_EXCLUSAO  = '3';

  /**
   * Constantes para as naturezas.
   * @type {number}
   */
  const NATUREZA_CREDITO       = '1';
  const NATUREZA_REDUCAO       = '2';
  const NATUREZA_OUTRAS_FONTES = '3';
  const NATUREZA_INDISPONIVEL  = '4';

  var oGet = js_urlToObject();

  /**
   * Objetos com os botões da tela.
   */
  var oBtnRISalvar     = $('btnRISalvar');
  var oBtnRIExcluir    = $('btnRIExcluir');
  var oBtnRIPesquisar  = $('btnRIPesquisar');
  var oBtnFichasLancar = $('btnFichasLancar');
  var oBtnFichasSalvar = $('btnFichasSalvar');

  /**
   * Objetos com os campos da primeira aba.
   */
  var oCodigo   = $('codigo');
  var oNumero   = $('numero');
  var oProcesso = $('processo');
  var oData     = $('data');
  var oObjetivo = $('objetivo');

  /**
   * Objetos com os campos da segunda aba.
   */
  var oFichaCodigo    = $('ficha_codigo');
  var oFichaDescricao = $('ficha_descricao');
  var oNatureza       = $('natureza');
  var oValor          = $('valor');
  var oMes            = $('mes');

  /**
   * Colunas com totalizadores.
   */
  var oTotalCredito      = $('total_credito');
  var oTotalReducao      = $('total_reducao');
  var oTotalIndisponivel = $('total_indisponivel');
  var oTotalOutrasFontes = $('total_outras_fontes');

  /**
   * Objeto com a linha que contém o campo mês.
   */
  var oLinhaMes = $('linha_mes');

  /**
   * Posições dos campos na linha da grid.
   */
  var iGridPosicaoDescricaoFicha    = 0;
  var iGridPosicaoDescricaoNatureza = 1;
  var iGridPosicaoValorFormatado    = 2;
  var iGridPosicaoDescricaoMes      = 3;
  var iGridPosicaoAcao              = 4;
  var iGridPosicaoCodigoFicha       = 5;
  var iGridPosicaoCodigoMes         = 6;
  var iGridPosicaoValor             = 7;
  var iGridPosicaoCodigoNatureza    = 8;

  /**
   * Iniciando variáveis necessárias.
   */
  var oGrid      = null;
  var aMeses     = ["-", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
  var aNaturezas = ["-", "Crédito", "Redução", "Outras Fontes", "Indisponível"];

  /**
   * Cria abas.
   */
  var oAbas = new DBAbas($('ctnAbasResolucaoFinanceira'));
  oAbas.adicionarAba('Resolução Inter-Administrativa', $('ctnResolucaoFinanceira'), true);

  var oAbaFichaRI            = oAbas.adicionarAba('Fichas', $('ctnFichaResolucaoFinanceira'));
  var oDivFichasRI           = $('Fichas');
  var sFunctionFichaOriginal = oDivFichasRI.onclick;

  /**
   * onclick da primeira aba.
   * @returns {boolean}
   */
  oDivFichasRI.onclick = function() {

    if (oCodigo.value == "" || oGet.opcao == OPCAO_EXCLUSAO) {
      return false;
    }

    sFunctionFichaOriginal();

    if (oGrid == null) {

      oGrid = new DBGrid('oGrid');
      oGrid.nameInstance = 'oGrid';
      oGrid.setHeader(['Ficha', 'Natureza', 'Valor', 'Mês', 'Ação', 'Código Ficha', 'Código Mês', 'Valor Não Formatado', 'Código Natureza']);
      oGrid.setCellWidth(['50%', '15%', '15%', '10%', '10%', '0%', '0%', '0%', '0%']);
      oGrid.setCellAlign(['left', 'center', 'right', 'center', 'center', 'left', 'left', 'left', 'left']);
      oGrid.setHeight(250);
      oGrid.show($('ctnGridFichasRI'));
      oGrid.showColumn(false, iGridPosicaoCodigoFicha + 1);
      oGrid.showColumn(false, iGridPosicaoCodigoMes + 1);
      oGrid.showColumn(false, iGridPosicaoValor + 1);
      oGrid.showColumn(false, iGridPosicaoCodigoNatureza + 1);
      oGrid.clearAll(true);

      buscarFichasGrid();
    }
  };

  /**
   * onchage do campo natureza.
   */
  oNatureza.onchange = function() {

    if (oNatureza.value == NATUREZA_CREDITO
        || oNatureza.value == NATUREZA_REDUCAO
        || oNatureza.value == NATUREZA_OUTRAS_FONTES) {

      oMes.value = 0;
      oLinhaMes.style.display = "table-row";
    }

    if (oNatureza.value == NATUREZA_INDISPONIVEL) {
      oLinhaMes.style.display = "none";
    }
  };

  /**
   * onchange do campo ficha.
   */
  oFichaCodigo.onchange = function() {
    return buscarFicha(false);
  };

  /**
   * onclick do salvar da primeira aba.
   */
  oBtnRISalvar.onclick = function() {


    if (oData.value == "") {
      return alert("Campo Data é de preenchimento obrigatório.");
    }

    if (oObjetivo.value == "") {
      return alert("Campo Objetivo é de preenchimento obrigatório.");
    }

    oParametros = {
      exec     : 'salvar',
      codigo   : oCodigo.value == "" ? null : oCodigo.value,
      processo : encodeURIComponent(tagString(oProcesso.value)),
      data     : oData.value.urlDecode(),
      objetivo : oObjetivo.value
    };

    oBtnRISalvar.disabled = true;

    new AjaxRequest(RPC, oParametros, function(oRetorno, lErro) {

      oBtnRISalvar.disabled = false;

      alert(oRetorno.mensagem.urlDecode());
      if (lErro) {
        return false;
      }

      oCodigo.value = oRetorno.codigo;
      oNumero.value = oRetorno.numero.urlDecode();
      oDivFichasRI.onclick();

    }).setMessage("Salvando Resolução Inter-Administrativa. Aguarde...").execute();
  };

  /**
   * onclick do excluir da primeira aba.
   */
  oBtnRIExcluir.onclick = function() {

    if (oCodigo.value == "") {
      return alert("A Resolução Inter-Administrativa deve ser informada para exclusão.");
    }

    oParametros = {
      exec   : "excluir",
      codigo : oCodigo.value
    };

    new AjaxRequest(RPC, oParametros, function(oRetorno, lErro) {

      alert(oRetorno.mensagem.urlDecode());

      if (!lErro) {

        limparTela();
        oBtnRIPesquisar.click();
      }

    }).setMessage("Aguarde, excluíndo Resolução Inter-Administrativa.").execute();
  };

  /**
   * onclick do pesquisar da primeira aba.
   */
  oBtnRIPesquisar.onclick = function() {

    var sArquivo = "func_resolucaointeradministrativa.php?funcao_js=parent.preencherRI|0";
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_resolucaointeradministrativa', sArquivo, 'Pesquisa de Resolução Inter-Administrativa', true);
  };

  /**
   * onclick do lançar da segunda aba.
   */
  oBtnFichasLancar.onclick = function() {

    if (oFichaCodigo.value == "") {

      oFichaDescricao.value = "";
      return alert("Campo Ficha é de preenchimento obrigatório.");
    }

    if (oNatureza.value == "" || oNatureza.value == 0) {
      return alert("Campo Natureza é de preenchimento obrigatório.");
    }

    if (oValor.value.getNumber() == "") {
      return alert("Campo Valor é de preenchimento obrigatório.");
    }

    if ((oNatureza.value == NATUREZA_CREDITO
         || oNatureza.value == NATUREZA_REDUCAO
         || oNatureza.value == NATUREZA_OUTRAS_FONTES
        ) && (oMes.value == "" || oMes.value == 0)) {
      return alert("Para Natureza de Crédito, Redução ou Outras Fontes é obrigatório informar o campo Mês.");
    }

    var iMes = getValorMes();
    var sMes = aMeses[oMes.value];
    var sButton = "<input type='button' value='Excluir' onclick='excluirFicha("+oGrid.getRows().length+")' />";
    oGrid.addRow([
                   oFichaDescricao.value,
                   aNaturezas[oNatureza.value],
                   oValor.value,
                   sMes,
                   sButton,
                   oFichaCodigo.value,
                   iMes,
                   oValor.value.getNumber(),
                   oNatureza.value
                 ]);
    oGrid.renderRows();
    oBtnFichasLancar.disabled = true;
    limparAbaFichas();
    atualizaTotalizadores();
  };

  /**
   * onclick do salvar da segunda aba.
   */
  oBtnFichasSalvar.onclick = function() {

    var nTotalReducao      = 0;
    var nTotalCredito      = 0;
    var nTotalIndisponivel = 0;
    var nValor = 0;

    var aFichas = [];

    oGrid.getRows().each(function(oRow) {

      nValor = new Number(oRow.aCells[iGridPosicaoValor].getValue());

      switch (oRow.aCells[iGridPosicaoCodigoNatureza].getValue()) {

        case NATUREZA_CREDITO:
          nTotalCredito += nValor;
          break;

        case NATUREZA_REDUCAO:
          nTotalReducao += nValor;
          break;

        case NATUREZA_INDISPONIVEL:
          nTotalIndisponivel += nValor;
          break;
      }

      aFichas[aFichas.length] = {
        valor           : oRow.aCells[iGridPosicaoValor].getValue().trim(),
        mes             : oRow.aCells[iGridPosicaoCodigoMes].getValue().trim(),
        natureza        : oRow.aCells[iGridPosicaoCodigoNatureza].getValue().trim(),
        fichafinanceira : oRow.aCells[iGridPosicaoCodigoFicha].getValue().trim()
      };
    });

    if (nTotalCredito.toFixed(2) != (nTotalReducao + nTotalIndisponivel).toFixed(2)) {
      return alert("Não é possível salvar a RI, pois o total de Créditos é diferente do total de Reduções.");
    }

    oParametros = {
      exec   : "salvarFichas",
      codigo : oCodigo.value,
      fichas : aFichas
    };

    new AjaxRequest(RPC, oParametros, function(oRetorno, lErro) {

      alert(oRetorno.mensagem.urlDecode());

      if (!lErro) {

        buscarFichasGrid();
      }

    }).setMessage("Aguarde, salvando fichas da RI.").execute();
  };

  oValor.onfocus = function() {
    this.value = this.value.getNumber() == 0 ? '' : this.value.getNumber();
  };

  oValor.onblur = function() {

    if (isNaN(this.value.getNumber())) {
      this.value = 0;
    }

    this.value = js_formatar(this.value, 'f', 2);
  };


  /**
   * Preencher a Grid de fichas com os valores retornados do RPC.
   * @param oRetorno object
   * @param lErro boolean
   */
  function preencherFichas(oRetorno, lErro) {

    if (lErro) {
      return alert(oRetorno.mensagem.urlDecode())
    }
    var sButton = "";
    var sMes    = "";

    oGrid.clearAll(true);
    oRetorno.fichas.each(function(oFicha) {

      iMes = oFicha.mes == "" ? 0 : oFicha.mes;
      sButton = "<input type='button' value='Excluir' onclick='excluirFicha("+oGrid.getRows().length+")' />";
      oGrid.addRow([
                     oFicha.descricao.urlDecode(),
                     aNaturezas[oFicha.natureza],
                     js_formatar(oFicha.valor, 'f', 2),
                     aMeses[iMes],
                     sButton,
                     oFicha.fichafinanceira,
                     iMes,
                     oFicha.valor,
                     oFicha.natureza
                   ]);
    });
    oGrid.renderRows();
    atualizaTotalizadores();
  }

  /**
   * Preenche os valores da RI com o retorno da lookup e invoca a buscar do restante dos valores.
   * @param iCodigo integer
   * @param lErro   boolean
   */
  function preencherRI(iCodigo, lErro) {

    limparTela();
    limparGrid();

    if (lErro) {
      iCodigo = '';
    }

    oBtnRISalvar.disabled = lErro;

    oCodigo.value = iCodigo;
    db_iframe_resolucaointeradministrativa.hide();
    buscarRI();
  }


  /**
   * Busca os valores dos campos da RI pelo código.
   */
  function buscarRI() {

    var iCodigo = oCodigo.value;
    if (iCodigo == '') {
      return alert("Código da RI não informado.");
    }

    oParametros = {
      exec   : 'getDados',
      codigo : iCodigo
    };

    new AjaxRequest(RPC, oParametros, function(oRetorno, lErro) {

      if (lErro) {
        return alert(oRetorno.mensagem.urlDecode());
      }

      oNumero.value   = oRetorno.numero.urlDecode();
      oProcesso.value = oRetorno.processo.urlDecode();
      oData.value     = oRetorno.data.urlDecode();
      oObjetivo.value = oRetorno.objetivo.urlDecode();

    }).setMessage("Aguarde, buscando informações da Resolução Inter-Administrativa.").execute();
  }

  /**
   * Excluí uma ficha da Grid.
   * @param iLinha integer Posição na grid.
   */
  function excluirFicha(iLinha) {

    oGrid.removeRow([iLinha]);
    var iIndice = 0;
    oGrid.getRows().each(function(oRow) {

      var sButton = "<input type='button' value='Excluir' onclick='excluirFicha("+iIndice+")' />";
      oRow.aCells[iGridPosicaoAcao].setContent(sButton);
      iIndice++;
    });
    oGrid.renderRows();
    atualizaTotalizadores();
  }

  /**
   * Busca as fichas para preencher a grid.
   */
  function buscarFichasGrid() {

    oParametros = {
      exec   : "getFichas",
      codigo : oCodigo.value
    };
    new AjaxRequest(RPC, oParametros, preencherFichas).setMessage("Aguarde, buscando fichas...").execute();
  }

  /**
   * Busca uma ficha na lookup.
   * @param lMostrar boolean
   */
  function buscarFicha(lMostrar) {

    var sPesquisa = "";
    var sCampos   = "|0|1";
    if (!lMostrar) {

      if (oFichaCodigo.value == "") {
        oFichaDescricao.value = "";
        return;
      }
      sCampos   = "";
      sPesquisa = "pesquisa_chave=" + oFichaCodigo.value+"&";
    }

    var sArquivo = "func_fichaorcamentoprogramacaofinanceira.php?"+sPesquisa+"funcao_js=parent.preencherFichaProgramacaoFinanceira"+sCampos;
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_fichaorcamentoprogramacaofinanceira', sArquivo, 'Pesquisa de Ficha', lMostrar);

  }

  /**
   * Preenche os campos da programação financeira com o retorno da lookup.
   * @param iCodigo integer
   * @param sDescricao string
   * @param lErro boolean
   */
  function preencherFichaProgramacaoFinanceira(iCodigo, sDescricao, lErro) {

    if (lErro) {
      iCodigo    = '';
    }

    oBtnFichasLancar.disabled = lErro;

    oFichaCodigo.value    = iCodigo;
    oFichaDescricao.value = sDescricao;
    db_iframe_fichaorcamentoprogramacaofinanceira.hide();
  }

  /**
   * Verifica se deve retornar o valor do campo mes ou vazio, a partir da natureza.
   * @returns {*}
   */
  function getValorMes() {

    if (oNatureza.value == NATUREZA_INDISPONIVEL) {
      return 0;
    }
    return oMes.value;
  }

  /**
   * Totaliza os valores e atualiza os campos.
   */
  function atualizaTotalizadores() {

    var nValor             = 0;
    var nTotalCredito      = 0;
    var nTotalReducao      = 0;
    var nTotalIndisponivel = 0;
    var nTotalOutrasFontes = 0;

    if (oGrid != null) {

      oGrid.getRows().each(function(oRow) {

        nValor = new Number(oRow.aCells[iGridPosicaoValor].getValue());

        switch (oRow.aCells[iGridPosicaoCodigoNatureza].getContent()) {

          case NATUREZA_CREDITO:
            nTotalCredito += nValor;
            break;

          case NATUREZA_REDUCAO:
            nTotalReducao += nValor;
            break;

          case NATUREZA_INDISPONIVEL:
            nTotalIndisponivel += nValor;
            break;

          case NATUREZA_OUTRAS_FONTES:
            nTotalOutrasFontes += nValor;
            break;
        }
      });
    }

    nTotalCredito += nTotalOutrasFontes;

    oTotalCredito.innerHTML      = js_formatar(nTotalCredito, 'f');
    oTotalReducao.innerHTML      = js_formatar(nTotalReducao, 'f');
    oTotalIndisponivel.innerHTML = js_formatar(nTotalIndisponivel, 'f');
    oTotalOutrasFontes.innerHTML = js_formatar(nTotalOutrasFontes, 'f');
  }

  /**
   * Limpa todos os campos da tela.
   */
  function limparTela() {

    limparAbaRI();
    limparAbaFichas();
  }

  /**
   * Limpa os campos da aba da RI.
   */
  function limparAbaRI() {

    oCodigo.value = '';
    oNumero.value = '';
    oProcesso.value = '';
    oData.value = '';
    oObjetivo.value = '';

    oBtnRISalvar.disabled = true;
  }

  /**
   * Limpa os campos da aba das fichas da RI.
   */
  function limparAbaFichas() {

    oFichaCodigo.value    = '';
    oFichaDescricao.value = '';
    oNatureza.value       = 0;
    oValor.value          = '';
    oMes.value            = 0;

    oLinhaMes.style.display = "table-row";
    oBtnFichasLancar.disabled = true;
  }

  /**
   * Limpa a grid.
   */
  function limparGrid() {

    if (oGrid != null) {

      oGrid.clearAll(true);
      oGrid = null;
    }
  }

  /**
   * Inicia a tela de acordo com a opção.
   */
  function iniciar() {

    limparTela();

    oBtnRISalvar.style.display    = (oGet.opcao != OPCAO_EXCLUSAO ? 'in-line' : 'none');
    oBtnRIExcluir.style.display   = (oGet.opcao == OPCAO_EXCLUSAO ? 'in-line' : 'none');
    oBtnRIPesquisar.style.display = (oGet.opcao != OPCAO_INCLUSAO ? 'in-line' : 'none');
    oBtnRISalvar.disabled         = oGet.opcao != OPCAO_INCLUSAO;
    oBtnFichasLancar.disabled     = true;

    if (oGet.opcao != OPCAO_INCLUSAO) {
      oBtnRIPesquisar.click();
    }
  }

  iniciar();

</script>
</body>
</html>