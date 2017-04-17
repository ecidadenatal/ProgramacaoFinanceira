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
   * Retorna o �ltimo m�s fechado. Retorna 0 caso n�o exista nenhum m�s fechado.
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
      throw new DBException("Houve um erro ao buscar o �ltimo m�s fechado para a Programa��o Financeira.");
    }

    return db_utils::fieldsMemory($rsFechamentoMensal, 0)->mes_fechado;
  }

  /**
   * Verifica se o m�s informado est� fechado para sua respectiva programa��o financeira.
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
   * Realiza o fechamento mensal da programa��o financeira.
   * @param int $iMes
   *
   * @throws BusinessException
   * @throws DBException
   * @throws ParameterException
   */
  public function fecharMes($iMes)  {

    $this->validaMes($iMes);

    if ($this->mesFechado($iMes)) {
      throw new BusinessException("N�o � poss�vel realizar o Fechamento Mensal, pois a Programa��o Financeira j� est� fechada para o m�s selecionado.");
    }

    $iProximoMesFechar = $this->getUltimoMesFechado() + 1;
    if ($iProximoMesFechar != $iMes) {

      $sMes = DBDate::getMesExtenso($iProximoMesFechar);
      throw new BusinessException("O fechamento mensal n�o pode ser realizado. O pr�ximo m�s a ser fechado deve ser {$sMes}.");
    }

    $oDaoFechamentoMensal = new cl_programacaofinanceirafechamentomensal();
    $oDaoFechamentoMensal->orcamentoprogramacaofinanceira = $this->oProgramacaoFinanceira->getCodigo();
    $oDaoFechamentoMensal->mes                            = $iMes;
    $oDaoFechamentoMensal->data                           = date("Y-m-d");
    $oDaoFechamentoMensal->usuario                        = db_getsession("DB_id_usuario");
    $oDaoFechamentoMensal->incluir(null);
    $this->iCodigo = $oDaoFechamentoMensal->sequencial;

    if ($oDaoFechamentoMensal->erro_status == 0) {
      throw new DBException("Erro ao Fechar o M�s para a Programa�ao Financeira\n{$oDaoFechamentoMensal->erro_msg}");
    }
  }

  /**
   * Verifica se o m�s informado � v�lido.
   * @param int $iMes
   *
   * @throws ParameterException
   */
  private function validaMes($iMes) {

    if (empty($iMes) || !is_numeric($iMes) || !Check::between($iMes, DBDate::JANEIRO, DBDate::DEZEMBRO)) {
      throw new ParameterException('Par�metro m�s n�o informado ou inv�lido.');
    }
  }

  /**
   * Retorna todos os meses do ano com a informa��o se est� fechado ou n�o.
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
