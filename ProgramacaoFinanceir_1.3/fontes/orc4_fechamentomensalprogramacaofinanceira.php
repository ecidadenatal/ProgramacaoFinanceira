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
require_once(modification("model/orcamento/ProgramacaoFinanceira/ProgramacaoFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FechamentoMensal.model.php"));

try {

  $iAno = db_getsession('DB_anousu');
  $oInstituicao = InstituicaoRepository::getInstituicaoByCodigo(db_getsession('DB_instit'));
  $oProgramacaoFinanceira = ProgramacaoFinanceira::getInstanciaPorInstituicaoAno($oInstituicao, $iAno);
} catch (Exception $e) {
  db_redireciona('db_erros.php?fechar=true&db_erro=' . $e->getMessage());
}

?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>

  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">

  <div class="container">

    <form id="formLiberacaoEmpenho">

      <div id="ctnCadastroAtividade">

        <fieldset>
          <legend class="bold">Fechamento Mensal da Programação Financeira</legend>
          <table style="width: 100%">

            <tr>
              <td>
                <label for="programacao_financeira" class="bold">Programação Financeira:</label>
              </td>
              <td>
                <?php $programacao_financeira           = $oProgramacaoFinanceira->getCodigo() ?>
                <?php $programacao_financeira_descricao = $oProgramacaoFinanceira->getDescricao() ?>
                <?php db_input('programacao_financeira', 10, 0, true, 'text', 22, 'style="width: 90px;" class="readonly"') ?>
                <?php db_input('programacao_financeira_descricao', 20, 0, true, 'text', 22, 'style="width: 300px;" class="readonly"') ?>
              </td>
            </tr>

            <tr>
              <td>
                <label for="mes" class="bold">Mês:</label>
              </td>
              <td>
                <?php $oFechamento = new FechamentoMensal($oProgramacaoFinanceira) ?>
                <select style="width: 90px;" id="mes" name="mes">
                <?php foreach ($oFechamento->getMeses() as $iChave => $oMes) : ?>
                  <?php $sDisabled = $oMes->lFechado ? 'disabled="disabled"' : null ?>
                  <option value="<?php echo $iChave ?>" <?php echo $sDisabled ?>><?php echo $oMes->sDescricao ?></option>
                <?php endforeach ?>
                </select>
              </td>
            </tr>

          </table>
        </fieldset>

        <p class="text-center">
          <input type="button" id="btnSalvar" value="Salvar" />
        </p>

      </div>

    </form>

  </div>

  <?php db_menu() ?>

  <script type="text/javascript">
  document.observe('dom:loaded', function(){

    const URL_RPC = 'orc4_programacaofinanceira.RPC.php';

    var oBtnSalvar = $('btnSalvar');

    oBtnSalvar.observe('click', function(oEvent){

      var sMensagem = "Ao realizar o Fechamento Mensal da Programação Financeira não será mais possível " +
      "efetuar autorizações e emissões de empenho, autorizações de repasse ou pagamentos no mês selecionado. Deseja prosseguir?";

      if (empty($F('mes'))) {

        alert('Campo Mês é de preenchimento obrigatório.');
        return;
      }

      if (!confirm(sMensagem)) {
        return;
      }

      var oButton = oEvent.target;
      var oParametros = {
        'exec'                   : 'fecharMes',
        'mes'                    : $F('mes'),
        'programacao_financeira' : $F('programacao_financeira')
      };

      oButton.setAttribute('disabled', 'disabled');
      new AjaxRequest(URL_RPC, oParametros, function(oRetorno, lErro){

        oButton.removeAttribute('disabled');
        return alert(oRetorno.mensagem.urlDecode());
      }).setMessage('Aguarde, salvando os dados...')
        .execute();
    });

  });
  </script>
</body>
</html>

