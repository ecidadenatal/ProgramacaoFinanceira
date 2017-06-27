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

  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">

  <div id="ctnProgramacaoFinanceira">
    <fieldset style="width: 600px">
      <legend class="bold">Situação da Programação Financeira</legend>
      <table>
        <tr>
          <td class="bold">
            <label for="programacao_financeira">Programação Financeira:</label>
          </td>
          <td>
            <?php
            $Sprogramacao_financeira = "Programação Financeira";
            db_input("programacao_financeira_codigo", 10, 1, true, "text", 3);
            db_input("programacao_financeira_descricao", 41, 1, true, "text", 3);
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
            $aSituacao = array(
              ProgramacaoFinanceira::SITUACAO_EM_ELABORACAO => 'Em elaboração',
              ProgramacaoFinanceira::SITUACAO_FECHADA       => 'Fechada',
              ProgramacaoFinanceira::SITUACAO_APROVADA      => 'Aprovada'
            );
            db_select('situacao', $aSituacao, true, 1);
            ?>
          </td>
        </tr>
      </table>
    </fieldset>
    <p style="text-align: center">
      <input type="button" id="btnSalvar" value="Salvar" onclick="salvar()" disabled />
    </p>
  </div>

</div>
<?php
db_menu();
?>
</body>
</html>

<script>
  
  /**
   * @todo
   * alterar o nome do arquivo
   */
  const ARQUIVO_RPC  = 'orc4_programacaofinanceira.RPC.php';
  var iSituacaoAtual = 0;

  pesquisar();
  
  function pesquisar() {
    var sArquivo = "func_orcamentoprogramacaofinanceira.php?funcao_js=parent.preencherProgramacao|0";
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_programacaofinanceira', sArquivo, 'Programação Financeira', true);
  }

  function preencherProgramacao(iCodigo) {

    $('programacao_financeira_codigo').value = iCodigo;
    db_iframe_programacaofinanceira.hide();
    carregarSituacaoProgramacaoFinanceira();
  }
  
  /**
   * Carrega a situação da Programação Financeira selecionada
   */
  function carregarSituacaoProgramacaoFinanceira() {

    if ($F('programacao_financeira_codigo') == '') {

      $('btnSalvar').disabled = true;
      return alert("Programação Financeira não informada.");
    }
    $('btnSalvar').disabled = false;

    new AjaxRequest(
      ARQUIVO_RPC,
      {exec : 'getProgramacaoFinanceira', codigo : $F('programacao_financeira_codigo')},
      function (oRetorno, lErro) {

        if (lErro) {
          return alert(oRetorno.mensagem.urlDecode());
        }

        $('programacao_financeira_descricao').value = oRetorno.descricao.urlDecode();
        $('situacao').value = oRetorno.situacao;
        iSituacaoAtual      = oRetorno.situacao;
      }
    ).setMessage('Aguarde, carregando informações....').execute();
  }
  
  function salvar() {

    if ($F('programacao_financeira_codigo') == '') {
      $('programacao_financeira_codigo').focus();
      return alert("Campo Programação Financeira é de preenchimento obrigatório.");
    }

    if($F('situacao') == '') {
      $('situacao').focus();
      return alert('Campo Situação é de preenchimento obrigatório');
    }

    var oParametros = {
      exec      : 'alterarSituacao',
      codigo    : $F('programacao_financeira_codigo'),
      situacao  : $F('situacao')
    };
    
    new AjaxRequest(
      ARQUIVO_RPC,
      oParametros,
      function (oRetorno, lErro) {

        if (lErro) {
          $('situacao').value = iSituacaoAtual;
        } else {
          iSituacaoAtual = $('situacao').value;
        }

        return alert(oRetorno.mensagem.urlDecode());
      }
    ).setMessage('Aguarde, salvando informações...').execute();
    
  }
</script>