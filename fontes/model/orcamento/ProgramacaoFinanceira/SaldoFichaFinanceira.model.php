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

require_once(modification("model/orcamento/ProgramacaoFinanceira/ProgramacaoFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceiraRepository.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaFinanceiraValor.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/ResolucaoInteradministrativa.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/SaldoFichaFinanceira.model.php"));
require_once(modification("model/orcamento/ProgramacaoFinanceira/FichaResolucaoInteradministrativa.model.php"));

/**
 * Class SaldoFichaFinanceira
 */
class SaldoFichaFinanceira {

  /**
   * @var FichaFinanceira
   */
  private $oFichaFinanceira;

  /**
   * @var int
   */
  private $iMes;

  /**
   * @var float
   */
  private $nValorPago;

  /**
   * @var float
   */
  private $nValorLiquidado;

  /**
   * @var float
   */
  private $nValorEmpenhado;

  /**
   * @var float
   */
  private $nSaldoReservado;

  /**
   * @var float
   */
  private $nValorRepassado;

  /**
   * @var SaldoFichaFinanceira
   */
  private $oSaldoMesAnterior;

  /**
   * SaldoFichaFinanceira constructor.
   *
   * @param FichaFinanceira $oFicha
   * @param int             $iMes
   * @throws ParameterException
   */
  public function __construct(FichaFinanceira $oFicha, $iMes) {

    if (empty($oFicha)) {
      throw new ParameterException("Parâmetro Ficha Financeira é obrigatória.");
    }

    if (empty($iMes) || !is_numeric($iMes) || !Check::between($iMes, DBDate::JANEIRO, DBDate::DEZEMBRO)) {
      throw new ParameterException('Parâmetro mês não informado ou inválido.');
    }

    if ($iMes != DBDate::JANEIRO) {
      $this->oSaldoMesAnterior = new SaldoFichaFinanceira($oFicha, $iMes - 1);
    }

    $this->oFichaFinanceira = $oFicha;
    $this->iMes             = $iMes;
  }

  /**
   * @return float
   * @throws Exception
   * @throws ParameterException
   */
  public function getPrevisao() {

    $aFichaFinanceiraValor = $this->oFichaFinanceira->getValores();

    return $aFichaFinanceiraValor[$this->iMes - 1]->getValor();
  }

  /**
   * Retorna o saldo de crédito para a ficha no mês (Crédito + Outras Fontes).
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  public function getCredito() {

    return ResolucaoInteradministrativa::getRemanejamentoPorNatureza(
      $this->oFichaFinanceira,
      ResolucaoInteradministrativa::NATUREZA_CREDITO,
      $this->iMes
    ) + ResolucaoInteradministrativa::getRemanejamentoPorNatureza(
      $this->oFichaFinanceira,
      ResolucaoInteradministrativa::NATUREZA_OUTRAS_FONTES,
      $this->iMes
    );
  }

  /**
   * Retorna o valor de redução para a ficha no mês.
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  public function getReducao() {

    return ResolucaoInteradministrativa::getRemanejamentoPorNatureza(
      $this->oFichaFinanceira,
      ResolucaoInteradministrativa::NATUREZA_REDUCAO,
      $this->iMes
    );
  }

  /**
   * Busca o saldo reservado
   * @return float
   * @throws Exception
   */
  public function getSaldoReservado() {

    if (is_null($this->nSaldoReservado)) {

      $aWhere = array(
        "o80_anousu = {$this->oFichaFinanceira->getAno()}",
        "o58_anousu = {$this->oFichaFinanceira->getAno()}",
        "o58_orgao = {$this->oFichaFinanceira->getCodigoOrgao()}",
        "o58_unidade = {$this->oFichaFinanceira->getCodigoUnidade()}",
        "o58_codigo = {$this->oFichaFinanceira->getCodigoRecurso()}",
        "o58_localizadorgastos = {$this->oFichaFinanceira->getCodigoAnexo()}",
        "{$this->iMes} >= extract(month from o80_dtini)::integer",
        "{$this->iMes} <= extract(month from o80_dtfim)::integer"
      );

      $oDaoOrcreserva = new cl_orcreserva();
      $sSqlReservado  = $oDaoOrcreserva->sql_query_reservas(null,
                                                            "coalesce( sum(o80_valor), 0) as valor_total",
                                                            null,
                                                            implode(" and ", $aWhere)
      );

      $rsReservado    = db_query($sSqlReservado);

      if (empty($rsReservado)) {
        throw new Exception("Erro ao buscar o saldo reservado.");
      }

      $this->nSaldoReservado = db_utils::fieldsMemory($rsReservado, 0)->valor_total;
    }
    return $this->nSaldoReservado;
  }

  /**
   * @return float
   */
  public function getSaldoEmpenharAnterior() {

    if ($this->iMes != DBDate::JANEIRO) {

      return $this->oSaldoMesAnterior->getSaldoEmpenharAnterior()
             + $this->oSaldoMesAnterior->getPrevisao()
             + $this->oFichaFinanceira->getValorProgramar($this->iMes-1)
             + $this->oSaldoMesAnterior->getCredito()
             - $this->oSaldoMesAnterior->getReducao()
             - $this->oSaldoMesAnterior->getValorEmpenhado();
    }

    return 0;
  }

  /**
   * Busca saldo do valor empenhado no mês.
   *
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  public function getValorEmpenhado() {

    if (is_null($this->nValorEmpenhado)) {
      $this->nValorEmpenhado = $this->getSaldoLancamentosPorTipo($this->iMes, 10, 11);
    }
    return $this->nValorEmpenhado;
  }

  /**
   * Busca saldo do valor liquidado.
   *
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  public function getValorLiquidado() {

    if (is_null($this->nValorLiquidado)) {
      $this->nValorLiquidado = $this->getSaldoLancamentosPorTipo($this->iMes, 20, 21);
    }
    return $this->nValorLiquidado;
  }

  /**
   * Busca saldo do valor pago.
   *
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  public function getValorPago() {

    $this->nValorPago = $this->getSaldoLancamentosPorTipo($this->iMes, 30, 31) + $this->getValorAtualizadoNaAgenda();
    return $this->nValorPago;
  }

  /**
   * Cálcula o valor atualizado e não pago na agenda para a ficha financeira no mês.
   * @return float
   * @throws DBException
   */
  private function getValorAtualizadoNaAgenda() {

    $oDaoEmpAgeMov = new cl_empagemov();

    $iAno     = $this->oFichaFinanceira->getAno();
    $iOrgao   = $this->oFichaFinanceira->getCodigoOrgao();
    $iUnidade = $this->oFichaFinanceira->getCodigoUnidade();
    $iRecurso = $this->oFichaFinanceira->getCodigoRecurso();
    $iAnexo   = $this->oFichaFinanceira->getCodigoAnexo();

    $sCampos = " coalesce(sum(e81_valor), 0) as valor ";
    $sWhere  = " o58_anousu = {$iAno} and o58_orgao = {$iOrgao} and o58_unidade = {$iUnidade}  ";
    $sWhere .= " and o58_codigo = {$iRecurso} and o58_localizadorgastos = {$iAnexo} ";
    $sWhere .= " and extract(month from e86_data) = {$this->iMes} ";
    $sWhere .= " and extract(year  from e86_data) = {$iAno} ";
    $sWhere .= " and e81_cancelado is null ";
    $sWhere .= " and k12_sequencial is null ";

    $sSql = $oDaoEmpAgeMov->sql_query_movimentos_atualizados_nao_pagos($sCampos, $sWhere);
    $rsDadosAgenda = $oDaoEmpAgeMov->sql_record($sSql);

    if ($rsDadosAgenda === false || $oDaoEmpAgeMov->numrows != 1) {
      throw new DBException("Houve um erro ao calcular o valor atualizado na agenda.");
    }

    return (float) db_utils::fieldsMemory($rsDadosAgenda, 0)->valor;
  }

  /**
   * @return float
   * @throws Exception
   */
  public function getValorRepassado() {

    $aWhere = array(
      "solicitacaorepasse.unidade_orgao  = {$this->oFichaFinanceira->getCodigoOrgao()}",
      "solicitacaorepasse.unidade_codigo = {$this->oFichaFinanceira->getCodigoUnidade()}",
      "solicitacaorepasse.recurso = {$this->oFichaFinanceira->getCodigoRecurso()}",
      "solicitacaorepasse.anexo   = {$this->oFichaFinanceira->getCodigoAnexo()}",
      "solicitacaorepasse.unidade_anousu = {$this->oFichaFinanceira->getAno()}",
      "extract(year  from autorizacaorepasse.data) = {$this->oFichaFinanceira->getAno()}",
      "extract(month from autorizacaorepasse.data) = {$this->iMes}"
    );
    $oDaoRepasse = new cl_solicitacaorepasse();
    $sSqlRepasse = $oDaoRepasse->sql_query_autorizacao('coalesce(sum(valor), 0) as repassado', implode(' and ', $aWhere));
    $rsRepasse   = $oDaoRepasse->sql_record($sSqlRepasse);
    if (!$rsRepasse || $oDaoRepasse->erro_status == "0" || $oDaoRepasse->numrows == 0) {
      throw new Exception("Não foi possível carregar o valor repassado da ficha financeira.");
    }
    return (float)db_utils::fieldsMemory($rsRepasse, 0)->repassado;
  }

  /**
   * @return float
   */
  public function getSaldoRepassarAnterior() {

    if ($this->iMes != DBDate::JANEIRO) {

      return $this->oSaldoMesAnterior->getSaldoRepassarAnterior()
             + $this->oSaldoMesAnterior->getPrevisao()
             + $this->oFichaFinanceira->getValorProgramar()
             + $this->oSaldoMesAnterior->getCredito()
             - $this->oSaldoMesAnterior->getReducao()
             - $this->oSaldoMesAnterior->getValorRepassado();
    }

    return 0;
  }

  /**
   * @return float
   */
  public function getSaldoPagarAnterior() {

    if ($this->iMes != DBDate::JANEIRO) {

      return $this->oSaldoMesAnterior->getSaldoPagarAnterior()
             + $this->oSaldoMesAnterior->getValorRepassado()
             - $this->oSaldoMesAnterior->getValorPago();
    }

    return 0;
  }

  /**
   * @return float
   */
  public function getValorDisponivelParaEmpenho() {

    return $this->getPrevisao() + $this->getCredito() + $this->oFichaFinanceira->getValorProgramar($this->iMes)
           - $this->getReducao() + $this->getSaldoEmpenharAnterior()
           - $this->getValorEmpenhado();
  }

  /**
   * @return float
   */
  public function getValorDisponivelParaRepasse() {

    return $this->getPrevisao() + $this->getCredito() + $this->oFichaFinanceira->getValorProgramar($this->iMes)
           - $this->getReducao() + $this->getSaldoRepassarAnterior()
           - $this->getValorRepassado();
  }

  /**
   * @return float
   */
  public function getValorDisponivelParaPagamento() {

    return $this->getValorRepassado() + $this->getSaldoPagarAnterior() - $this->getValorPago();
  }

  /**
   * @return boolean
   */
  public function possuiSaldoPagar() {
    return $this->getValorDisponivelParaPagamento() >= 0;
  }

  /**
   * Busca o saldo para um tipo de lançamento dentro do mês.
   * @param int $iMes                   Mês
   * @param int $iTipoLancamento        Tipo de lançamento.
   * @param int $iTipoLancamentoEstorno Tipo de lançamento de estorno.
   *
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  private function getSaldoLancamentosPorTipo($iMes, $iTipoLancamento, $iTipoLancamentoEstorno) {

    $iMes                   = (int) $iMes;
    $iTipoLancamento        = (int) $iTipoLancamento;
    $iTipoLancamentoEstorno = (int) $iTipoLancamentoEstorno;

    if (empty($iMes) || $iMes < DBDate::JANEIRO || $iMes > DBDate::DEZEMBRO) {
      throw new ParameterException("Parâmetro mês não informado ou inválido.");
    }

    if (empty($iTipoLancamento)) {
      throw new ParameterException("Parâmetro tipo de lançamento não informado.");
    }

    if (empty($iTipoLancamentoEstorno)) {
      throw new ParameterException("Parâmetro tipo de lançamento de estorno não informado.");
    }

    $iAno       = $this->oFichaFinanceira->getAno();
    $iOrgao     = $this->oFichaFinanceira->getCodigoOrgao();
    $iUnidade   = $this->oFichaFinanceira->getCodigoUnidade();
    $iRecurso   = $this->oFichaFinanceira->getCodigoRecurso();
    $iAnexo     = $this->oFichaFinanceira->getCodigoAnexo();
    $iInstuicao = $this->oFichaFinanceira->getProgramacaoFinanceira()->getInstituicao()->getCodigo();

    $sDataInicio = "{$iAno}-{$iMes}-01";
    $sDataFim    = "{$iAno}-{$iMes}-" . DBDate::getQuantidadeDiasMes($iMes, $iAno);

    $sCampos  = "coalesce(sum(case when c53_tipo = {$iTipoLancamento} then c70_valor else 0 end), 0) as valor, ";
    $sCampos .= "coalesce(sum(case when c53_tipo = {$iTipoLancamentoEstorno} then c70_valor else 0 end), 0) as valor_estorno ";

    $sWhere  = "c53_tipo in ({$iTipoLancamento}, {$iTipoLancamentoEstorno}) and o58_anousu = {$iAno} ";
    $sWhere .= " and o58_orgao = {$iOrgao} and o58_unidade = {$iUnidade}";
    $sWhere .= " and o58_codigo = {$iRecurso} and o58_localizadorgastos = {$iAnexo} and o58_instit = {$iInstuicao} ";
    $sWhere .= " and c70_data between '{$sDataInicio}' and '{$sDataFim}'";

    $oDaoEmpenho = new cl_empempenho();

    /* Alterado para quando for lançamento de empenho, buscar os valores das cotas */
    if ($iTipoLancamento == 10 && $iTipoLancamentoEstorno == 11) {

      $sSqlempenhado  = "select coalesce(sum(case when c53_tipo = 10 then e05_valor    else 0 end), 0) as valor, ";
      $sSqlempenhado .= "       coalesce(sum(case when c53_tipo = 11 then valoranulado else 0 end), 0) as valor_estorno  ";
      $sSqlempenhado .= "from (select ";
      $sSqlempenhado .= "       c53_tipo, e05_valor, valoranulado ";
      $sSqlempenhado .= "from empempenho ";
      $sSqlempenhado .= "     inner join conlancamemp      on conlancamemp.c75_numemp = empempenho.e60_numemp ";
      $sSqlempenhado .= "     inner join conlancam         on conlancam.c70_codlan    = conlancamemp.c75_codlan ";
      $sSqlempenhado .= "     inner join conlancamdoc      on conlancamdoc.c71_codlan = conlancamemp.c75_codlan ";
      $sSqlempenhado .= "     inner join orcdotacao        on orcdotacao.o58_anousu   = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot ";
      $sSqlempenhado .= "     inner join conhistdoc        on conhistdoc.c53_coddoc   = conlancamdoc.c71_coddoc ";
      $sSqlempenhado .= "     inner join db_config         on db_config.codigo        = empempenho.e60_instit ";
      $sSqlempenhado .= "     inner join empenhocotamensal on empenhocotamensal.e05_numemp = empempenho.e60_numemp and empenhocotamensal.e05_mes = {$iMes} ";
      $sSqlempenhado .= "      left join plugins.empenhocotamensalanulacao ON plugins.empenhocotamensalanulacao.empenhocotamensal = empenhocotamensal.e05_sequencial ";
      $sSqlempenhado .= "where c53_tipo in ({$iTipoLancamento}, {$iTipoLancamentoEstorno}) and o58_anousu = {$iAno} ";
      $sSqlempenhado .= "      and o58_orgao = {$iOrgao} and o58_unidade = {$iUnidade} and o58_codigo = {$iRecurso} ";
      $sSqlempenhado .= "      and o58_localizadorgastos = {$iAnexo} and o58_instit = {$iInstuicao} ";
      $sSqlempenhado .= "group by c53_tipo, e05_valor, valoranulado) T ";

      $rsLancamentos  = $oDaoEmpenho->sql_record($sSqlempenhado);
    } else {
      $sSqlempenhado = $oDaoEmpenho->sql_query_buscaliquidacoes(null, $sCampos, null, $sWhere);
      $rsLancamentos = $oDaoEmpenho->sql_record($sSqlempenhado);
    }

    if ($rsLancamentos === false || $oDaoEmpenho->numrows != 1) {
      throw new DBException("Houve um erro ao buscar o valor para os lançamentos: " . $oDaoEmpenho->erro_msg);
    }

    $oTotalLancamentos = db_utils::fieldsMemory($rsLancamentos, 0);

    return (float) ($oTotalLancamentos->valor - $oTotalLancamentos->valor_estorno);
  }

  /**
   * Verifica se a Ficha Financeira possui saldo para empenhar o valor informado para o mês.
   *
   * @param float $nValor
   *
   * @return bool
   * @throws BusinessException
   */
  public function possuiSaldoEmpenhar($nValor) {

    if ($nValor < 0) {
      throw new BusinessException("Valor a ser empenhado não pode ser menor que zero.");
    }
    return round($this->getValorDisponivelParaEmpenho(), 2) >= round($nValor, 2);
  }

  /**
   * Verifica se a Ficha Financeira possui o saldo para repassar o valor informado para o mes.
   * @param float $nValor
   *
   * @return bool
   * @throws BusinessException
   */
  public function possuiSaldoRepassar($nValor)  {

    if ($nValor < 0) {
      throw new BusinessException("Valor a ser repassado não pode ser menor que zero.");
    }

    return round($this->getValorDisponivelParaRepasse(), 2) >= round($nValor, 2);
  }
}
