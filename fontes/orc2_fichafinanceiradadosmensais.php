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
  <style>
  .coluna-valor {
    outline: 1px solid #ccc;
    background: #fff;
    width: 150px;
    text-align: right;
  }
  .coluna-label {
   font-weight: bold;
   text-align: left;
   width: 100px;
  }
  .coluna-label,
  .coluna-valor {
    padding: 4px;
  }
  .topo-tabela-consulta {
    text-align: center;
  }
  .tabela-consulta, .tabela-consulta td {
    border: none;
  }
  </style>
</head>
<body>

  <div class="container">

    <?php
    $aMeses = array(
      'Janeiro', 'Fevereiro', 'Março',
      'Abril', 'Maio', 'Junho',
      'Julho', 'Agosto', 'Setembro',
      'Outubro', 'Novembro', 'Dezembro',
    );
    ?>

    <fieldset>
      <legend>Valores Mensais</legend>
      <table class="tabela-consulta">
        <tr>
        <?php foreach ($aMeses as $iChave => $sMes) : ?>
          <?php if ($iChave % 3 == 0 && $iChave > 0) : ?>
            </tr><tr>
          <?php endif ?>

          <td class="coluna-label"><?php echo $sMes ?>:</td>
          <td class="coluna-valor" id="valor-mes-<?php echo $iChave ?>">&nbsp;</td>
        <?php endforeach ?>
        </tr>
      </table>
    </fieldset>

  </div>

  <script type="text/javascript">
  document.observe('dom:loaded', function(){

    var oGet = js_urlToObject();
    const URL_RPC = "orc4_programacaofinanceira.RPC.php";

    var oParametros = {
      'exec'    : 'getDetalhesFicha',
      'codigo'  : oGet.codigo
    };

    new AjaxRequest(URL_RPC, oParametros, function(oRetorno, lErro) {

      if (lErro) {
        alert(oRetorno.mensagem.urlDecode());
        return;
      }

      $(oRetorno.meses).each(function (oObject, iKey) {
        $('valor-mes-' + iKey).innerHTML = js_formatar(oObject.valor, 'f');
      });

    }).setMessage('Aguarde, carregando dados da ficha...')
      .asynchronous(false)
      .execute();
  });
  </script>

</body>
</html>
