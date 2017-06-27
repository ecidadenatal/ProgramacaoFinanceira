<?php

require_once(modification("model/orcamento/ProgramacaoFinanceira/ProgramacaoFinanceira.model.php"));
class FechamentoMensal {

  /**
   * @var int
   */
  private $iCodigo;

  /**
   * @var ProgramacaoFinanceira
   */
  private $oProgramacaoFinanceira;

  public function __construct(ProgramacaoFinanceira $oProgramacaoFinanceira) {
    $this->oProgramacaoFinanceira = $oProgramacaoFinanceira;
  }

  /**
   * Retorna o último mês fechado. Retorna 0 caso não exista nenhum mês fechado.
   * @return int
   * @throws DBException
   */
  public function getUltimoMesFechado() {

    $sCampos = " coalesce(max(mes), 0) as mes_fechado ";
    $sWhere  = " orcamentoprogramacaofinanceira = " . $this->oProgramacaoFinanceira->getCodigo();

    $oDaoFechamentoMensal = new cl_programacaofinanceirafechamentomensal();
    $sSqlFechamentoMensal = $oDaoFechamentoMensal->sql_query_file(null, $sCampos, null, $sWhere);
    $rsFechamentoMensal   = db_query($sSqlFechamentoMensal);

    if ($rsFechamentoMensal === false || pg_num_rows($rsFechamentoMensal) != 1) {
      throw new DBException("Houve um erro ao buscar o último mês fechado para a Programação Financeira.");
    }

    return db_utils::fieldsMemory($rsFechamentoMensal, 0)->mes_fechado;
  }

  /**
   * Verifica se o mês informado está fechado para sua respectiva programação financeira.
   *
   * @param int $iMes
   *
   * @return bool
   * @throws DBException
   * @throws ParameterException
   */
  public function mesFechado($iMes) {

    $this->validaMes($iMes);

    $sCampos = " sequencial ";
    $sWhere  = " mes = {$iMes} and orcamentoprogramacaofinanceira = " . $this->oProgramacaoFinanceira->getCodigo();

    $oDaoFechamentoMensal = new cl_programacaofinanceirafechamentomensal();
    $sSqlFechamentoMensal = $oDaoFechamentoMensal->sql_query_file(null, $sCampos, null, $sWhere);
    $rsFechamentoMensal   = db_query($sSqlFechamentoMensal);

    if ($rsFechamentoMensal === false) {
      throw new DBException("Houve um erro ao verificar o fechamento mensal.");
    }

    return pg_num_rows($rsFechamentoMensal) != 0;
  }

  /**
   * Realiza o fechamento mensal da programação financeira.
   * @param int $iMes
   *
   * @throws BusinessException
   * @throws DBException
   * @throws ParameterException
   */
  public function fecharMes($iMes)  {

    $this->validaMes($iMes);

    if ($this->mesFechado($iMes)) {
      throw new BusinessException("Não é possível realizar o Fechamento Mensal, pois a Programação Financeira já está fechada para o mês selecionado.");
    }

    $iProximoMesFechar = $this->getUltimoMesFechado() + 1;
    if ($iProximoMesFechar != $iMes) {

      $sMes = DBDate::getMesExtenso($iProximoMesFechar);
      throw new BusinessException("O fechamento mensal não pode ser realizado. O próximo mês a ser fechado deve ser {$sMes}.");
    }

    $oDaoFechamentoMensal = new cl_programacaofinanceirafechamentomensal();
    $oDaoFechamentoMensal->orcamentoprogramacaofinanceira = $this->oProgramacaoFinanceira->getCodigo();
    $oDaoFechamentoMensal->mes                            = $iMes;
    $oDaoFechamentoMensal->data                           = date("Y-m-d");
    $oDaoFechamentoMensal->usuario                        = db_getsession("DB_id_usuario");
    $oDaoFechamentoMensal->incluir(null);
    $this->iCodigo = $oDaoFechamentoMensal->sequencial;

    if ($oDaoFechamentoMensal->erro_status == 0) {
      throw new DBException("Erro ao Fechar o Mês para a Programaçao Financeira\n{$oDaoFechamentoMensal->erro_msg}");
    }
  }

  /**
   * Verifica se o mês informado é válido.
   * @param int $iMes
   *
   * @throws ParameterException
   */
  private function validaMes($iMes) {

    if (empty($iMes) || !is_numeric($iMes) || !Check::between($iMes, DBDate::JANEIRO, DBDate::DEZEMBRO)) {
      throw new ParameterException('Parâmetro mês não informado ou inválido.');
    }
  }

  /**
   * Retorna todos os meses do ano com a informação se está fechado ou não.
   *
   * @return stdClass[]
   */
  public function getMeses() {

    $aMeses = array();
    for ($iIndice = DBDate::JANEIRO; $iIndice <= DBDate::DEZEMBRO; $iIndice++) {

      $oStdMes = new stdClass;
      $oStdMes->lFechado   = $this->mesFechado($iIndice);
      $oStdMes->sDescricao = DBDate::getMesExtenso($iIndice);

      $aMeses[$iIndice] = $oStdMes;
    }

    return $aMeses;
  }

}
