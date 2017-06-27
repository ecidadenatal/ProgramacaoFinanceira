<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBselller Servicos de Informatica
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
require_once(modification("classes/db_resolucaointeradministrativa_classe.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/ResolucaoInteradministrativa.model.php"));

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
  <script type="text/javascript" src="scripts/EmissaoRelatorio.js"></script>

  <link href="estilos.css" rel="stylesheet" type="text/css">

  <style type="text/css">
    #ctnResolucaoInteradministrativa >fieldset >table >tbody >tr >td { width: 115px; }
    .aprovacao { display: none; }
  </style>

</head>
<body class="body-default">
  <div class="container">

    <div id="ctnResolucaoInteradministrativa">

      <fieldset style="width: 400px">

        <legend class="bold">Situação da Resolução Inter-Administrativa</legend>
        <table style="margin: 0px auto; ">

          <tr>
            <td class="bold">
              <label for="resolucao_interadministrativa">Número da RI:</label>
            </td>
            <td>
              <?php
              $sResolucao_interadministrativa = "Número da RI:";
              db_input("numero", 10, 1, true, "text", 3, "style='width: 120px;' readonly='readonly' class='readonly'");
              db_input("codigo", 10, 1, true, "hidden", 3);
              ?>
            </td>
          </tr>

          <tr>
            <td class="bold">
              <label for="descricao">Situação:</label>
            </td>
            <td>
              <?php
              $sSituacao = "Situação";
              db_select('situacao', ResolucaoInteradministrativa::getSituacoes(), true, 1, " onChange='verificaSituacao()' style='width: 120px;'");
              ?>
            </td>
          </tr>

          <tr class="aprovacao">
            <td class="bold">
              <label for="descricao">Tipo de Aprovação:</label>
            </td>
            <td>
              <?php
              $sAprovacao = "Tipo de Aprovação";
              $aAprovacao = array('0' => 'Selecione');
              $aAprovacao = array_merge($aAprovacao, ResolucaoInteradministrativa::getTiposAprovacao());
              db_select('tipo_aprovacao', $aAprovacao, true, 1, " style='width: 120px;' ");
              ?>
            </td>
          </tr>

          <tr class="aprovacao">
            <td class="bold">
              <label for="descricao">Data da Aprovação:</label>
            </td>
            <td>
              <?php
              $sData = "Data da Aprovação";
              db_inputdata("data_aprovacao", "", "", "", true, 'text', 1, "class='field-size2'");
              ?>
            </td>
          </tr>
        </table>

      </fieldset>

      <p style="text-align: center">
        <input type="button" id="btnSalvar" value="Salvar" disabled />
        <input type="button" id="btnPesquisar" value="Pesquisar" />
        <input type="button" id="btnEmitir" value="Emitir" style="display: none;" />
      </p>

    </div>

  </div>
  <?php db_menu() ?>
  <script>
    const ARQUIVO_RPC       = 'orc4_resolucaointeradministrativa.RPC.php';
    const SITUACAO_APROVADA = <?php echo ResolucaoInteradministrativa::SITUACAO_APROVADA; ?>;

    var oBtnSalvar     = $('btnSalvar');
    var oBtnPesquisar  = $('btnPesquisar');
    var oBtnEmitir     = $('btnEmitir');

    oBtnSalvar.observe('click', salvar);
    oBtnPesquisar.observe('click', pesquisar);
    oBtnEmitir.observe('click', emitir);
    pesquisar();

    function pesquisar() {

      var sArquivo = "func_resolucaointeradministrativa.php?funcao_js=parent.preencherResolucao|0|1";
      js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_resolucaointeradministrativa', sArquivo, 'Resolução Inter-Administrativa', true);
    }

    function emitir() {

      var sUrl = 'orc2_documentoresolucaointeradministrativa002.php';
      var oParametros = {
        codigo : $('codigo').getValue()
      };
      var oEmissao = new EmissaoRelatorio(sUrl, oParametros);
      oEmissao.open();
    }

    function preencherResolucao(iCodigo, sNumero) {

      $('codigo').value = iCodigo;
      $('numero').value = sNumero;
      db_iframe_resolucaointeradministrativa.hide();
      carregarSituacaoResolucaoInteradministrativa();
    }

    function verificaSituacao() {

      $$('.aprovacao').each(function(e) {
        e.style.display = 'none';
      });

      if($('situacao').value == SITUACAO_APROVADA) {

        $$('.aprovacao').each(function(e) {
          e.style.display = 'table-row';
        });
      }
    }

    /**
     * Carrega a situação da Resolução Inter-Administrativa selecionada
     */
    function carregarSituacaoResolucaoInteradministrativa() {

      if ($F('codigo') == '') {

        oBtnSalvar.disabled = true;
        return alert("Resolução Inter-Administrativa não informada.");
      }
      oBtnSalvar.disabled = false;

      var oParametros = {
        exec   : 'getDados',
        codigo : $F('codigo')
      };

      oBtnEmitir.hide();

      /**
       * limpa campos para carregar a Resolução
       */
      $('data_aprovacao').value = '';
      var tipo_aprovacao_opcoes = $$('select#tipo_aprovacao option');
      tipo_aprovacao_opcoes[0].selected = true;

      new AjaxRequest(ARQUIVO_RPC, oParametros, function (oRetorno, lErro) {

          if (lErro) {
            return alert(oRetorno.mensagem.urlDecode());
          }

          $('situacao').value       = oRetorno.situacao;
          $('tipo_aprovacao').value = oRetorno.tipo_aprovacao;
          if (!empty(oRetorno.data_aprovacao)) {
            $('data_aprovacao').value = oRetorno.data_aprovacao.urlDecode();
          }
          if (oRetorno.situacao == SITUACAO_APROVADA) {
            oBtnEmitir.show();
          }
          verificaSituacao();
        }
      ).setMessage('Aguarde, carregando informações...')
       .execute();
    }

    function salvar() {

      if ($F('codigo') == '') {

        $('codigo').focus();
        return alert("Campo Resolução Inter-Administrativa é de preenchimento obrigatório.");
      }

      if($F('situacao') == '') {

        $('situacao').focus();
        return alert('Campo Situação é de preenchimento obrigatório');
      }

      var oParametros = {
        exec      : 'alterarSituacao',
        codigo    : $F('codigo'),
        situacao  : $F('situacao')
      };

      if ($F('situacao') == SITUACAO_APROVADA) {

        if ($F('tipo_aprovacao') == '' || $F('tipo_aprovacao') == '0') {
          return alert('Campo Tipo de Aprovação é de preenchimento obrigatório.');
        }
        if ($F('data_aprovacao') == '') {
          return alert('Campo Data de Aprovação é de preenchimento obrigatório.');
        }
        oParametros.data_aprovacao = $F('data_aprovacao');
        oParametros.tipo_aprovacao = $F('tipo_aprovacao');
      }

      new AjaxRequest(ARQUIVO_RPC, oParametros, function (oRetorno, lErro) {

          if (!lErro) {

            if (oParametros.situacao == SITUACAO_APROVADA) {
              oBtnEmitir.show();
            }
          }

          return alert(oRetorno.mensagem.urlDecode());
        }
      ).setMessage('Aguarde, salvando informações...').execute();
    }
  </script>
</body>
</html>

