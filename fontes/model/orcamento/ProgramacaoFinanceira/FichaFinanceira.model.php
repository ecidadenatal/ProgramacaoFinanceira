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
 * Class FichaFinanceira
 */
class FichaFinanceira {

  /**
   * @var integer
   */
  private $iCodigo;

  /**
   * @var integer
   */
  private $iAno;

  /**
   * @var integer
   */
  private $iCodigoProgramacaoFinanceira;

  /**
   * @var integer
   */
  private $iCodigoOrgao;

  /**
   * @var integer
   */
  private $iCodigoUnidade;

  /**
   * @var integer
   */
  private $iCodigoRecurso;

  /**
   * @var integer
   */
  private $iCodigoAnexo;

  /**
   * @var float
   */
  private $nValorIndisponivel;

  /**
   * @var float
   */
  private $nValorProgramar;

  /**
   * @var float
   */
  private $nValorOrcado;

  /**
   * @var ProgramacaoFinanceira
   */
  private $oProgramacaoFinanceira;

  /**
   * @var Orgao
   */
  private $oOrgao;

  /**
   * @var Unidade
   */
  private $oUnidade;

  /**
   * @var Recurso
   */
  private $oRecurso;

  /**
   * @var LocalizadorGastos
   */
  private $oAnexo;

  /**
   * @var array
   */
  private $aValores;

  /**
   * FichaFinanceira constructor.
   *
   * @param null $iCodigo
   */
  public function __construct($iCodigo = null) {

    if (!empty($iCodigo)) {

      $oDaoFicha = new cl_fichaorcamentoprogramacaofinanceira();
      $sSqlFicha = $oDaoFicha->sql_query_file($iCodigo);
      $rsFicha   = $oDaoFicha->sql_record( $sSqlFicha );

      if ($oDaoFicha->numrows == 0) {
        throw new Exception("Ficha Financeira não encontrada.");
      }

      $oDadosFicha = db_utils::fieldsMemory($rsFicha, 0);

      $this->iCodigo            = $iCodigo;
      $this->iAno               = $oDadosFicha->ano;
      $this->iCodigoOrgao       = $oDadosFicha->orgao;
      $this->iCodigoUnidade     = $oDadosFicha->unidade;
      $this->iCodigoRecurso     = $oDadosFicha->recurso;
      $this->iCodigoAnexo       = $oDadosFicha->anexo;
      $this->nValorIndisponivel = $oDadosFicha->valorindisponivel;
      $this->nValorProgramar    = $oDadosFicha->valoraprogramar;
      $this->nValorOrcado       = $oDadosFicha->valororcado;
      $this->iCodigoProgramacaoFinanceira = $oDadosFicha->orcamentoprogramacaofinanceira;
    }
  }

  /**
   * @return integer
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * @param integer $iCodigo
   */
  public function setCodigo($iCodigo) {
    $this->iCodigo = $iCodigo;
  }

  /**
   * @return float
   */
  public function getValorIndisponivel() {
    return (float) $this->nValorIndisponivel;
  }

  /**
   * Retorna o saldo do valor indisponível,
   * @return float
   */
  public function getSaldoIndisponivel() {

    return (float) $this->nValorIndisponivel -
           ResolucaoInteradministrativa::getRemanejamentoPorNatureza(
             $this,
             ResolucaoInteradministrativa::NATUREZA_INDISPONIVEL
           );
  }

  /**
   * @param float $nValorIndisponivel
   */
  public function setValorIndisponivel($nValorIndisponivel) {
    $this->nValorIndisponivel = $nValorIndisponivel;
  }

  /**
   * @return float
   */
  public function getValorProgramar($iMes = null) {
    if ($iMes != null && $iMes != 1) {
      return 0;
    }
    return (float) $this->nValorProgramar;
  }

  /**
   * @param float $nValorProgramar
   */
  public function setValorProgramar($nValorProgramar) {
    $this->nValorProgramar = $nValorProgramar;
  }

  /**
   * @return ProgramacaoFinanceira
   */
  public function getProgramacaoFinanceira() {

    if (empty($this->oProgramacaoFinanceira) && !empty($this->iCodigoProgramacaoFinanceira)) {
      $this->oProgramacaoFinanceira = new ProgramacaoFinanceira($this->iCodigoProgramacaoFinanceira);
    }

    return $this->oProgramacaoFinanceira;
  }

  /**
   * @param ProgramacaoFinanceira $oProgramacaoFinanceira
   */
  public function setProgramacaoFinanceira(ProgramacaoFinanceira $oProgramacaoFinanceira) {
    $this->oProgramacaoFinanceira = $oProgramacaoFinanceira;
  }

  /**
   * @return Orgao
   */
  public function getOrgao() {

    if (empty($this->oOrgao) && !empty($this->iCodigoOrgao) && !empty($this->iAno)) {
      $this->oOrgao = OrgaoRepository::getOrgaoPorCodigoAno($this->iCodigoOrgao, $this->iAno);
    }

    return $this->oOrgao;
  }

  /**
   * @param Orgao $oOrgao
   */
  public function setOrgao(Orgao $oOrgao) {
    $this->oOrgao = $oOrgao;
  }

  /**
   * @return Unidade
   */
  public function getUnidade() {

    if (empty($this->oUnidade) && !empty($this->iCodigoUnidade) && !empty($this->iAno)) {
      $this->oUnidade = new Unidade($this->iAno, $this->getOrgao()->getCodigoOrgao(), $this->iCodigoUnidade);
    }

    return $this->oUnidade;
  }

  /**
   * @param Unidade $oUnidade
   */
  public function setUnidade(Unidade $oUnidade) {
    $this->oUnidade = $oUnidade;
  }

  /**
   * @return Recurso
   */
  public function getRecurso() {

    if (empty($this->oRecurso) && !empty($this->iCodigoRecurso)) {
      $this->oRecurso = RecursoRepository::getRecursoPorCodigo($this->iCodigoRecurso);
    }

    return $this->oRecurso;
  }

  /**
   * @param Recurso $oRecurso
   */
  public function setRecurso(Recurso $oRecurso) {
    $this->oRecurso = $oRecurso;
  }

  /**
   * @return LocalizadorGastos
   */
  public function getAnexo() {

    if (empty($this->oAnexo) && !empty($this->iCodigoAnexo)) {
      $this->oAnexo = LocalizadorGastosRepository::getLocalizadorGastosPorCodigo($this->iCodigoAnexo);
    }

    return $this->oAnexo;
  }

  /**
   * @param LocalizadorGastos $oAnexo
   */
  public function setAnexo(LocalizadorGastos $oAnexo) {
    $this->oAnexo = $oAnexo;
  }

  /**
   * @return integer
   */
  public function getAno() {
    return $this->iAno;
  }

  /**
   * @param integer $iAno
   */
  public function setAno($iAno) {
    $this->iAno = $iAno;
  }

  /**
   * @param integer $iCodigoProgramacaoFinanceira
   */
  public function setCodigoProgramacaoFinanceira($iCodigoProgramacaoFinanceira) {
    $this->iCodigoProgramacaoFinanceira = $iCodigoProgramacaoFinanceira;
  }

  /**
   * @param integer $iCodigoOrgao
   */
  public function setCodigoOrgao($iCodigoOrgao) {
    $this->iCodigoOrgao = $iCodigoOrgao;
  }

  /**
   * @param integer $iCodigoUnidade
   */
  public function setCodigoUnidade($iCodigoUnidade) {
    $this->iCodigoUnidade = $iCodigoUnidade;
  }

  /**
   * @param integer $iCodigoRecurso
   */
  public function setCodigoRecurso($iCodigoRecurso) {
    $this->iCodigoRecurso = $iCodigoRecurso;
  }

  /**
   * @param integer $iCodigoAnexo
   */
  public function setCodigoAnexo($iCodigoAnexo) {
    $this->iCodigoAnexo = $iCodigoAnexo;
  }

  /**
   * @return integer
   */
  public function getCodigoProgramacaoFinanceira() {

    if (!empty($this->oProgramacaoFinanceira)) {
      $this->iCodigoProgramacaoFinanceira = $this->oProgramacaoFinanceira->getCodigo();
    }

    return $this->iCodigoProgramacaoFinanceira;
  }

  /**
   * @return integer
   */
  public function getCodigoOrgao() {

    if (!empty($this->oOrgao)) {
      $this->iCodigoOrgao = $this->oOrgao->getCodigoOrgao();
    }

    return $this->iCodigoOrgao;
  }

  /**
   * @return integer
   */
  public function getCodigoUnidade() {

    if (!empty($this->oUnidade)) {
      $this->iCodigoUnidade = $this->oUnidade->getCodigoUnidade();
    }

    return $this->iCodigoUnidade;
  }

  /**
   * @return integer
   */
  public function getCodigoRecurso() {

    if (!empty($this->oRecurso)) {
      $this->iCodigoRecurso = $this->oRecurso->getCodigo();
    }

    return $this->iCodigoRecurso;
  }

  /**
   * @return integer
   */
  public function getCodigoAnexo() {

    if (!empty($this->oAnexo)) {
      $this->iCodigoAnexo = $this->oAnexo->getSequencial();
    }

    return $this->iCodigoAnexo;
  }

  /**
   * @param FichaFinanceiraValor[] $aValores
   */
  public function setValores($aValores) {
    $this->aValores = $aValores;
  }

  /**
   * @return float
   */
  public function getValorOrcado() {
    return (float) $this->nValorOrcado;
  }

  /**
   * @param float $nValorOrcado
   */
  public function setValorOrcado($nValorOrcado) {
    $this->nValorOrcado = $nValorOrcado;
  }

  /**
   * @return FichaFinanceiraValor[]
   */
  public function getValores() {

    if (is_null($this->aValores) && !empty($this->iCodigo)) {

      $oDaoValores = new cl_fichaorcamentoprogramacaofinanceiravalor();
      $sSqlValores = $oDaoValores->sql_query_file( null, "*", "mes", "fichaorcamentoprogramacaofinanceira = {$this->iCodigo}");
      $rsValores   = db_query( $sSqlValores );

      if (empty($rsValores)) {
        throw new Exception("Erro ao buscar valores da Ficha Financeira.");
      }

      $this->aValores = db_utils::makeCollectionFromRecord($rsValores, function($oItem) {

        $oFichaValor = new FichaFinanceiraValor();
        $oFichaValor->setMes($oItem->mes);
        $oFichaValor->setValor($oItem->valor);

        return $oFichaValor;
      });
    }

    return $this->aValores;
  }

  public function salvar() {

    $aSituacoes = array(ProgramacaoFinanceira::SITUACAO_FECHADA, ProgramacaoFinanceira::SITUACAO_APROVADA);
    if (in_array($this->getProgramacaoFinanceira()->getSituacao(), $aSituacoes)) {
      throw new BusinessException("Não é possível alterar os dados da Programação Financeira com situação Aprovada ou Fechada.");
    }

    $oDaoFicha = new cl_fichaorcamentoprogramacaofinanceira();
    $oDaoFicha->sequencial = $this->getCodigo();
    $oDaoFicha->orcamentoprogramacaofinanceira = $this->getCodigoProgramacaoFinanceira();
    $oDaoFicha->orgao = $this->getCodigoOrgao();
    $oDaoFicha->unidade = $this->getCOdigoUnidade();
    $oDaoFicha->recurso = $this->getCodigoRecurso();
    $oDaoFicha->anexo = $this->getCodigoAnexo();
    $oDaoFicha->ano = $this->getAno();
    $oDaoFicha->valorindisponivel = $this->getValorIndisponivel();
    $oDaoFicha->valoraprogramar = $this->getValorProgramar();
    $oDaoFicha->valororcado = $this->getValorOrcado();

    if ($this->getCodigo() != '') {
      $oDaoFicha->alterar($this->getCodigo());
    } else {
      $oDaoFicha->incluir();
    }

    $this->iCodigo = $oDaoFicha->sequencial;

    if ($oDaoFicha->erro_status == 0) {
      throw new DBException("Erro ao Salvar dados da Ficha Financeira.");
    }

    /**
     * Salva os valores
     */
    if (!is_null($this->aValores)) {

      $oDaoValores = new cl_fichaorcamentoprogramacaofinanceiravalor();
      $oDaoValores->excluir(null, "fichaorcamentoprogramacaofinanceira = {$this->getCodigo()}");

      foreach ($this->aValores as $oValor) {

        $oDaoValores->sequencial = null;
        $oDaoValores->fichaorcamentoprogramacaofinanceira = $this->getCodigo();
        $oDaoValores->mes = $oValor->getMes();
        $oDaoValores->valor = $oValor->getValor();

        $oDaoValores->incluir();

        if ($oDaoValores->erro_status == 0) {
          throw new DBException("Erro ao Salvar valores da Ficha Financeira.");
        }
      }
    }
  }

  /**
   * @throws Exception
   * @return boolean
   */
  public function excluir() {

    if (!$this->getCodigo()) {
      return false;
    }

    $oDaoValores = new cl_fichaorcamentoprogramacaofinanceiravalor();
    $oDaoValores->excluir(null, "fichaorcamentoprogramacaofinanceira = {$this->getCodigo()}");

    if ($oDaoValores->erro_status == 0) {
      throw new Exception("Não foi possível excluir a ficha financeira.");
    }

    $oDaoFicha = new cl_fichaorcamentoprogramacaofinanceira();
    $oDaoFicha->excluir($this->getCodigo());

    if ($oDaoFicha->erro_status == 0) {
      throw new Exception("Não foi possível excluir a ficha financeira.");
    }

    $this->iCodigo  = null;
    $this->aValores = array();

    return true;
  }

  /**
   * Constrói a descrição da Ficha Financeira
   * @return string
   */
  public function getDescricao() {

    $sDescricao = str_pad($this->getOrgao()->getCodigoOrgao(), 2, "0", STR_PAD_LEFT) . '.'
                  . str_pad($this->getUnidade()->getCodigoUnidade(), 3, "0", STR_PAD_LEFT)
                  . ' - ' . $this->getRecurso()->getDescricao() . ' / ' . $this->getAnexo()->getDescricao();
    return $sDescricao;
  }

  /**
   * @param $iMes
   * @throws ParameterException
   * @return SaldoFichaFinanceira
   */
  public function getSaldoNoMes($iMes) {

    if (!Check::between($iMes, DBDate::JANEIRO, DBDate::DEZEMBRO) ) {
      throw new ParameterException("Mês [{$iMes}] informado inválido.");
    }
    return new SaldoFichaFinanceira($this, $iMes);
  }
}
