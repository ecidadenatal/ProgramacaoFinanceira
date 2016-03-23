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

require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_conecta_plugin.php");
require_once modification("libs/db_sessoes.php");
require_once modification("dbforms/db_funcoes.php");
require_once modification("libs/JSON.php");
require_once modification("model/orcamento/ProgramacaoFinanceira/ResolucaoInteradministrativa.model.php");
require_once(modification("model/orcamento/ProgramacaoFinanceira/ProgramacaoFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceiraValor.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/SaldoFichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceiraRepository.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaResolucaoInteradministrativa.model.php"));

$oParam = json_decode(str_replace("\\","",$_POST["json"]));

$oRetorno = new stdClass;
$oRetorno->mensagem = '';
$oRetorno->erro     = false;

try {

  db_inicio_transacao();

  switch($oParam->exec) {

    case "getDados":

      $oResolucaoInteradministrativa = new ResolucaoInteradministrativa((int) $oParam->codigo);
      $oRetorno->numero   = urlencode("{$oResolucaoInteradministrativa->getNumero()}/{$oResolucaoInteradministrativa->getAno()}");
      $oRetorno->processo = urlencode($oResolucaoInteradministrativa->getProcesso());
      $oRetorno->data     = urlencode($oResolucaoInteradministrativa->getData()->getDate(DBDate::DATA_PTBR));
      $oRetorno->objetivo = urlencode($oResolucaoInteradministrativa->getObjetivo());
      $oRetorno->situacao = $oResolucaoInteradministrativa->getSituacao();
      $oRetorno->data_aprovacao = '';

      if ($oResolucaoInteradministrativa->getDataAprovacao() != null) {
        $oRetorno->data_aprovacao = urlencode(
          $oResolucaoInteradministrativa->getDataAprovacao()->getDate(DBDate::DATA_PTBR)
        );
      }
      $oRetorno->tipo_aprovacao = $oResolucaoInteradministrativa->getTipoAprovacao();

      break;

    case "getFichas":

      $oResolucaoInteradministrativa = new ResolucaoInteradministrativa((int) $oParam->codigo);
      $aFichas = $oResolucaoInteradministrativa->getFichas();

      $aFichasRetorno = array();
      foreach ($aFichas as $oFicha) {

        $oFichaRetorno = new stdClass;
        $oFichaRetorno->codigo          = $oFicha->getCodigo();
        $oFichaRetorno->mes             = $oFicha->getMes();
        $oFichaRetorno->natureza        = $oFicha->getNatureza();
        $oFichaRetorno->fichafinanceira = $oFicha->getFichaFinanceira()->getCodigo();
        $oFichaRetorno->valor           = urlencode($oFicha->getValor());
        $oFichaRetorno->descricao       = urlencode($oFicha->getFichaFinanceira()->getDescricao());

        $aFichasRetorno[] = $oFichaRetorno;
      }

      $oRetorno->fichas = $aFichasRetorno;

      break;

    case 'salvar' :

      $iCodigo = !empty($oParam->codigo) ? $oParam->codigo : null;
      $oData   = new DBDate($oParam->data);
      $iAno    = db_getsession('DB_anousu');

      $oResolucaoInteradministrativa = new ResolucaoInterAdministrativa($iCodigo);
      $oResolucaoInteradministrativa->setProcesso(urlDecode(utf8_decode(db_stdClass::db_stripTagsJsonSemEscape($oParam->processo))));
      $oResolucaoInteradministrativa->setObjetivo(urlDecode(utf8_decode(db_stdClass::db_stripTagsJsonSemEscape($oParam->objetivo))));
      $oResolucaoInteradministrativa->setAno($iAno);
      $oResolucaoInteradministrativa->setData($oData);
      $oResolucaoInteradministrativa->salvar();

      $oRetorno->numero   = urlencode("{$oResolucaoInteradministrativa->getNumero()}/{$oResolucaoInteradministrativa->getAno()}");
      $oRetorno->codigo   = $oResolucaoInteradministrativa->getCodigo();
      $oRetorno->mensagem = "Resolução Inter-Administrativa salva com sucesso.";

      break;

    case 'excluir':

      $oResolucaoInteradministrativa = new ResolucaoInteradministrativa((int) $oParam->codigo);
      $oResolucaoInteradministrativa->excluir();
      $oRetorno->mensagem = "Resolução Inter-Administrativa excluída com sucesso.";

      break;

    case 'salvarFichas' :

      $oResolucaoInteradministrativa = new ResolucaoInteradministrativa((int) $oParam->codigo);

      if (count($oParam->fichas) === 0) {
        throw new ParameterException('Não há fichas lançadas para serem salvas.');
      }

      foreach ($oParam->fichas as $oFicha) {

        $oFichaFinanceira = new FichaFinanceira($oFicha->fichafinanceira);
        $oFichaRI = new FichaResolucaoInteradministrativa;
        $oFichaRI->setFichaFinanceira($oFichaFinanceira);
        $oFichaRI->setNatureza($oFicha->natureza);
        $oFichaRI->setMes($oFicha->mes);
        $oFichaRI->setValor($oFicha->valor);
        $oFichaRI->setResolucaoInteradministrativa($oResolucaoInteradministrativa);
        $oResolucaoInteradministrativa->adicionarFicha($oFichaRI);
      }

      $oResolucaoInteradministrativa->salvar();
      $oRetorno->mensagem = "Fichas da RI salvas com sucesso.";

      break;

    case 'alterarSituacao':

      $iSituacao      = (int) $oParam->situacao;
      $oDataAprovacao = null;

      $oResolucaoInteradministrativa = new ResolucaoInteradministrativa((int) $oParam->codigo);

      if ($iSituacao === ResolucaoInteradministrativa::SITUACAO_APROVADA) {

        $oDataAprovacao = new DBDate($oParam->data_aprovacao);
        $oDataAtual     = new DBDate(date('Y-m-d', db_getsession("DB_datausu")));

        if ($oDataAprovacao->getTimeStamp() > $oDataAtual->getTimestamp()) {
          throw new BusinessException("Data de aprovação não pode maior que a data atual.");
        }

        $oResolucaoInteradministrativa->setTipoAprovacao((int) $oParam->tipo_aprovacao);
      }

      $oResolucaoInteradministrativa->alterarSituacao($iSituacao, $oDataAprovacao);
      $oRetorno->mensagem = "Situação da Resolução Inter-Administrativa alterada com sucesso.";

      break;

    default:
      throw new Exception("Nenhuma opção definida.");
  }

  db_fim_transacao(false);

} catch (Exception $e) {

  $oRetorno->erro     = true;
  $oRetorno->mensagem = $e->getMessage();
  db_fim_transacao(true);
}

$oRetorno->mensagem = urlencode($oRetorno->mensagem);
echo json_encode($oRetorno);
