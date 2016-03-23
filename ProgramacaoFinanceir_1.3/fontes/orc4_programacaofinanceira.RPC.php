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
require_once(modification("libs/JSON.php"));

require_once(modification("model/orcamento/ProgramacaoFinanceira/ProgramacaoFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceiraValor.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/SaldoFichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/ResolucaoInteradministrativa.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/SaldoFichaFinanceira.model.php"));

$oParam = json_decode(str_replace("\\","",$_POST["json"]));

$oRetorno           = new stdClass();
$oRetorno->mensagem = '';
$oRetorno->erro     = false;

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case "salvar":

      $iCodigo     = (int) $oParam->codigo;
      $iAno        = (int) $oParam->ano;
      $iCronograma = (int) $oParam->cronograma_desembolso;
      $sDescricao  = trim($oParam->descricao);

      if (empty($iCodigo)) {
        $iCodigo = null;
      }

      if (empty($iAno)) {
        throw new ParameterException("Campo Ano é de preenchimento obrigatório.");
      }

      if (empty($iCronograma)) {
        throw new ParameterException("Campo Cronograma de Desembolso é de preenchimento obrigatório.");
      }

      if (empty($sDescricao)) {
        throw new ParameterException("Campo Descrição é de preenchimento obrigatório.");
      }

      $oCronograma  =  new cronogramaFinanceiro($iCronograma);
      $oInstituicao = InstituicaoRepository::getInstituicaoByCodigo(db_getsession('DB_instit'));

      if (empty($iCodigo) && ProgramacaoFinanceira::possuiProgramacaoInstituicaoAno($oInstituicao, $iAno)) {
        throw new Exception("Já existe uma Programação Financeira para a instituição e ano informados.");
      }

      $oProgramacao = new ProgramacaoFinanceira($iCodigo);

      $iSituacao = $oProgramacao->getSituacao();
      if (!empty($iSituacao) && in_array($iSituacao, array(ProgramacaoFinanceira::SITUACAO_APROVADA, ProgramacaoFinanceira::SITUACAO_FECHADA) )) {
        throw new BusinessException("Não é possível alterar os dados da Programação Financeira com situação Aprovada ou Fechada.");
      }


      $oProgramacao->setInstituicao($oInstituicao);
      $oProgramacao->setCronograma($oCronograma);
      $oProgramacao->setDescricao(db_stdClass::normalizeStringJsonEscapeString($sDescricao));
      $oProgramacao->setAno($iAno);
      $aDespesas = $oCronograma->getMetasDespesa(9);

      if (is_null($iCodigo)) {

        $aFichas = array();
        foreach ($aDespesas as $oDespesa) {

          $oFicha = new FichaFinanceira();

          $oFicha->setProgramacaoFinanceira($oProgramacao);
          $oFicha->setCodigoOrgao($oDespesa->o58_orgao);
          $oFicha->setCodigoUnidade($oDespesa->o58_unidade);
          $oFicha->setCodigoRecurso($oDespesa->o58_codigo);
          $oFicha->setCodigoAnexo($oDespesa->o58_localizadorgastos);
          $oFicha->setAno($iAno);
          $oFicha->setValorIndisponivel(0);
          $oFicha->setValorProgramar(0);

          $aFichasValor = array();
          $nFichaValorOrcado = 0;
          foreach ($oDespesa->aMetas->aMeses as $oMes) {

            $oFichaValor = new FichaFinanceiraValor();
            $oFichaValor->setMes($oMes->mes);
            $oFichaValor->setValor($oMes->valor);
            $aFichasValor[] = $oFichaValor;
            $nFichaValorOrcado += $oMes->valor;
          }

          $oFicha->setValorOrcado($nFichaValorOrcado);
          $oFicha->setValores($aFichasValor);
          $aFichas[] = $oFicha;
        }

        $oProgramacao->setFichas($aFichas);
      }

      $oProgramacao->salvar();

      $oRetorno->codigo   = $oProgramacao->getCodigo();
      $oRetorno->mensagem = 'Programação Financeira salva com sucesso.';

      break;

    case 'getProgramacaoFinanceira':

      if (empty($oParam->codigo)) {
        throw new ParameterException("O código da Programação Financeira não foi informado.");
      }

      $iCodigo      = (int) $oParam->codigo;
      $iAno         = db_getsession("DB_anousu");
      $iInstituicao = db_getsession('DB_instit');

      $oProgramacao = new ProgramacaoFinanceira($iCodigo);

      $oRetorno->codigo                       = $oProgramacao->getCodigo();
      $oRetorno->instituicao                  = $oProgramacao->getInstituicao()->getCodigo();
      $oRetorno->codigo_cronograma_desembolso = $oProgramacao->getCronograma()->getPerspectiva();
      $oRetorno->ano                          = $oProgramacao->getAno();
      $oRetorno->descricao                    = urlencode($oProgramacao->getDescricao());
      $oRetorno->situacao                     = $oProgramacao->getSituacao();

      break;

    case 'getFichas':

      if (empty($oParam->codigo)) {
        throw new ParameterException("O código da Programação Financeira não foi informado.");
      }

      $aWhereFichas = array();
      if (!empty($oParam->orgao)) {
        $aWhereFichas[] = "orgao = " . ((int) $oParam->orgao);
      }

      if (!empty($oParam->unidade)) {
        $aWhereFichas[] = "unidade = " . ((int) $oParam->unidade);
      }

      if (!empty($oParam->recurso)) {
        $aWhereFichas[] = "recurso = " . ((int) $oParam->recurso);
      }

      if (!empty($oParam->anexo)) {
        $aWhereFichas[] = "anexo = " . ((int) $oParam->anexo);
      }

      $oProgramacao = new ProgramacaoFinanceira($oParam->codigo);
      $aFichas      = $oProgramacao->getFichas(implode(" and ", $aWhereFichas));
      $oRetorno->fichas = array();

      foreach ($aFichas as $oFicha) {

        $nValorTotal = $oFicha->getValorIndisponivel() + $oFicha->getValorProgramar();
        foreach ($oFicha->getValores() as $oFichaFinanceiraMes) {
          $nValorTotal += $oFichaFinanceiraMes->getValor();
        }

        $aDadosFicha      = array(
          'codigo'       => $oFicha->getCodigo(),
          'orgao'        => $oFicha->getOrgao()->getCodigoOrgao(),
          'anexo'        => $oFicha->getAnexo()->getCodigo(),
          'unidade'      => $oFicha->getUnidade()->getCodigoUnidade(),
          'recurso'      => $oFicha->getRecurso()->getCodigo(),
          'descricao'    => urlencode($oFicha->getDescricao()),
          'valor_total'  => $nValorTotal,
          'valor_orcado' => $oFicha->getValorOrcado()
        );
        array_push($oRetorno->fichas, $aDadosFicha);
      }

      break;

    case 'getDetalhesFicha':

      $iCodigo = (int) $oParam->codigo;

      if (empty($iCodigo)) {
        throw new ParameterException("O código da Ficha Financeira não foi informado.");
      }

      $nValorTotal = 0;
      $aMesValores = array();
      $oFicha      = new FichaFinanceira($iCodigo);

      foreach ($oFicha->getValores() as $oFichaValor) {

        $oDadosValor        = new stdClass();
        $oDadosValor->mes   = urlencode(DBDate::getMesExtenso($oFichaValor->getMes()));
        $oDadosValor->valor = $oFichaValor->getValor();

        $aMesValores[$oFichaValor->getMes() - 1] = $oDadosValor;
        $nValorTotal += $oFichaValor->getValor();
      }

      $nValorTotal += $oFicha->getValorProgramar();
      $nValorTotal += $oFicha->getValorIndisponivel();

      $oRetorno->codigo             = $oFicha->getCodigo();
      $oRetorno->orgao              = $oFicha->getOrgao()->getCodigoOrgao();
      $oRetorno->orgao_descricao    = urlencode($oFicha->getOrgao()->getDescricao());
      $oRetorno->recurso            = $oFicha->getRecurso()->getCodigo();
      $oRetorno->recurso_descricao  = urlencode($oFicha->getRecurso()->getDescricao());
      $oRetorno->unidade            = $oFicha->getUnidade()->getCodigoUnidade();
      $oRetorno->unidade_descricao  = urlencode($oFicha->getUnidade()->getDescricao());
      $oRetorno->anexo              = $oFicha->getAnexo()->getCodigo();
      $oRetorno->anexo_descricao    = urlencode($oFicha->getAnexo()->getDescricao());
      $oRetorno->ano                = $oFicha->getAno();
      $oRetorno->valor_orcado       = $oFicha->getValorOrcado();
      $oRetorno->valor_indisponivel = $oFicha->getSaldoIndisponivel();
      $oRetorno->valor_programar    = $oFicha->getValorProgramar();
      $oRetorno->valor_total        = $nValorTotal;
      $oRetorno->descricao          = urlencode($oFicha->getDescricao());
      $oRetorno->meses              = $aMesValores;

      break;

    case 'alterarFicha':

      if (empty($oParam->iCodigoFicha)) {
        throw new ParameterException("O Código da Ficha não foi informado.");
      }

      if (empty($oParam->aValoresMensais) || count($oParam->aValoresMensais) != 12) {
        throw new ParameterException("Os valores mensais não foram informados.");
      }

      $aValores = array();
      foreach ($oParam->aValoresMensais as $iIndice => $nValorMensal) {

        $oFichaValor = new FichaFinanceiraValor();
        $oFichaValor->setMes(($iIndice+1));
        $oFichaValor->setValor($nValorMensal);
        $aValores[] = $oFichaValor;
      }

      $oFicha = new FichaFinanceira($oParam->iCodigoFicha);
      $oFicha->setValorIndisponivel($oParam->nValorIndisponivel);
      $oFicha->setValorProgramar($oParam->nValorProgramar);
      $oFicha->setValores($aValores);
      $oFicha->salvar();

      break;

    case 'excluir':

      $oProgramacao = new ProgramacaoFinanceira($oParam->codigo);
      $oProgramacao->excluir();
      $oRetorno->mensagem = "Programação Financeira excluída com sucesso.";

      break;

    case 'alterarSituacao':

      $oProgramacao = new ProgramacaoFinanceira($oParam->codigo);
      $oProgramacao->alterarSituacao($oParam->situacao);
      $oRetorno->mensagem = "Situação alterada com sucesso.";

      break;

    case 'getSaldosFichaMes':

      $iCodigo = isset($oParam->codigo) ? (int) $oParam->codigo : null;
      $iMes    = isset($oParam->mes)    ? (int) $oParam->mes    : null;

      if (empty($iCodigo)) {
        throw new ParameterException("O Código da Ficha Financeira não foi informado.");
      }

      if (empty($iMes)) {
        throw new ParameterException("O Mês de execução não foir informado.");
      }

      $oFicha      = new FichaFinanceira($iCodigo);
      $oSaldoFicha = new SaldoFichaFinanceira($oFicha, $iMes);

      $oRetorno->previsao                = $oSaldoFicha->getPrevisao();
      $oRetorno->credito                 = $oSaldoFicha->getCredito();
      $oRetorno->reducao                 = $oSaldoFicha->getReducao();
      $oRetorno->saldo_reservado         = $oSaldoFicha->getSaldoReservado();
      $oRetorno->saldo_empenhar_anterior = $oSaldoFicha->getSaldoEmpenharAnterior();
      $oRetorno->empenhado               = $oSaldoFicha->getValorEmpenhado();
      $oRetorno->liquidado               = $oSaldoFicha->getValorLiquidado();
      $oRetorno->repassado               = $oSaldoFicha->getValorRepassado();
      $oRetorno->saldo_repassar_anterior = $oSaldoFicha->getSaldoRepassarAnterior();
      $oRetorno->pago                    = $oSaldoFicha->getValorPago();
      $oRetorno->saldo_pagar_anterior    = $oSaldoFicha->getSaldoPagarAnterior();
      $oRetorno->disponivel_empenho      = $oSaldoFicha->getValorDisponivelParaEmpenho();
      $oRetorno->disponivel_repasse      = $oSaldoFicha->getValorDisponivelParaRepasse();
      $oRetorno->disponivel_pagamento    = $oSaldoFicha->getValorDisponivelParaPagamento();
      break;

    case 'verificarSaldoFichaFinanceira':


      $oInstituicao = InstituicaoRepository::getInstituicaoByCodigo(db_getsession('DB_instit'));
      $oData        = new DBDate(date('Y-m-d', db_getsession('DB_datausu')));

      $oProgramacaoFinanceira = ProgramacaoFinanceira::getInstanciaPorInstituicaoAno($oInstituicao, $oData->getAno());
      $oFechamentoMensal      = new FechamentoMensal($oProgramacaoFinanceira);
      if ($oFechamentoMensal->mesFechado($oData->getMes())) {

        $sMes = DBDate::getMesExtenso($oData->getMes());
        throw new BusinessException("Não é possível realizar Autorização de Repasse Financeiro, pois a Programação Financeira já está fechada para o mês de {$sMes}.");
      }

      $aFichas = array();
      foreach ($oParam->solicitacoes as $oStdSolicitacao) {

        $oSolicitacaoRepasse = new SolicitacaoRepasseFinanceiro($oStdSolicitacao->sCodigo);
        $iAnoSessao   = db_getsession('DB_anousu');
        $oInstituicao = InstituicaoRepository::getInstituicaoByCodigo(db_getsession('DB_instit'));
        $oUnidade     = $oSolicitacaoRepasse->getUnidade();
        $oOrgao       = $oUnidade->getOrgao();
        $oRecurso     = $oSolicitacaoRepasse->getRecurso();
        $oAnexo       = LocalizadorGastosRepository::getLocalizadorGastosPorCodigo($oSolicitacaoRepasse->getAnexo());

        $iHash = $oOrgao->getCodigoOrgao().$oUnidade->getCodigoUnidade().$oRecurso->getCodigo().$oAnexo->getCodigo();
        if (empty($aFichas[$iHash])) {

          $aFichas[$iHash] = new stdClass();
          $aFichas[$iHash]->ficha = FichaFinanceiraRepository::getInstanciaPorComposicao($oInstituicao, $iAnoSessao, $oOrgao, $oUnidade, $oRecurso, $oAnexo);
          $aFichas[$iHash]->valor = 0;
        }
        $aFichas[$iHash]->valor += $oSolicitacaoRepasse->getValor();
      }

      $oDataAtual   = new DBDate(date('Y-m-d', db_getsession('DB_datausu')));
      foreach ($aFichas as $iHash => $oStdFicha) {

        $oSaldoFicha = $oStdFicha->ficha->getSaldoNoMes($oDataAtual->getMes());
        if ( !$oSaldoFicha->possuiSaldoRepassar($oStdFicha->valor) ) {

          $sData = "{$oDataAtual->getMes()} - ".DBDate::getMesExtenso($oDataAtual->getMes());
          throw new Exception(
            "A ficha {$oStdFicha->ficha->getDescricao()} não possui Saldo Disponível para Repasse no mês {$sData}."
            . "\nSaldo disponível: " . number_format($oSaldoFicha->getValorDisponivelParaRepasse(), 2, ",", ".")
          );
        }
      }

      break;

    case "fecharMes":

      if (empty($oParam->programacao_financeira)) {
        throw new ParameterException('Programação Financeira não informada.');
      }

      if (empty($oParam->mes)) {
        throw new ParameterException('Mês não informado.');
      }

      $oProgramacaoFinanceira = new ProgramacaoFinanceira($oParam->programacao_financeira);
      $oFechamento = new FechamentoMensal($oProgramacaoFinanceira);
      $oFechamento->fecharMes($oParam->mes);
      $oRetorno->mensagem = 'O fechamento foi efetuado com sucesso.';

      break;

    default:
      throw new Exception("Nenhuma opção definida.");
      break;

  }

  db_fim_transacao(false);
} catch (Exception $e) {

  $oRetorno->erro     = true;
  $oRetorno->mensagem = $e->getMessage();
  db_fim_transacao(true);
}
$oRetorno->mensagem = urlencode($oRetorno->mensagem);
echo json_encode($oRetorno);
