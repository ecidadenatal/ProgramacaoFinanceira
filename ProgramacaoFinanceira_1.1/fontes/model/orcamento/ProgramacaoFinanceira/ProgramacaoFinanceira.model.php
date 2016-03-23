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
 * Class ProgramacaoFinanceira
 */
class ProgramacaoFinanceira {

  const SITUACAO_EM_ELABORACAO = 1;
  const SITUACAO_FECHADA       = 2;
  const SITUACAO_APROVADA      = 3;

  /**
   * @var int
   */
  private $iCodigo;

  /**
   * @var int
   */
  private $iCronograma;

  /**
   * @var int
   */
  private $iInstituicao;

  /**
   * @var int
   */
  private $iAno;

  /**
   * @var string
   */
  private $sDescricao;

  /**
   * @var int
   */
  private $iSituacao;

  /**
   * @var FichaFinanceira[]
   */
  private $aFichas;

  /**
   * @var Instituicao
   */
  private $oInstituicao;

  /**
   * @var cronogramaFinanceiro
   */
  private $oCronograma;

  /**
   * ProgramacaoFinanceira constructor.
   *
   * @param int $iCodigo
   *
   * @throws DBException
   * @throws Exception
   */
  public function __construct($iCodigo = null) {

    $this->iCodigo        = $iCodigo;

    if (!empty($iCodigo)) {

      $oDaoProgramacao      = new cl_orcamentoprogramacaofinanceira();
      $sSqlBuscaProgramacao = $oDaoProgramacao->sql_query_file(null, "*", null, " sequencial = {$iCodigo} ");
      $rsBuscaProgramacao   = $oDaoProgramacao->sql_record($sSqlBuscaProgramacao);

      if ($rsBuscaProgramacao === false) {
        throw new DBException("Erro ao buscar a Programação Financeira.");
      }

      if ($oDaoProgramacao->numrows != 1) {
        throw new Exception("Programação Financeira não encontrada.");
      }

      $oStdProgramacao = db_utils::fieldsMemory($rsBuscaProgramacao, 0);

      $this->iCronograma  = $oStdProgramacao->cronograma;
      $this->iInstituicao = $oStdProgramacao->instituicao;
      $this->iAno         = $oStdProgramacao->ano;
      $this->sDescricao   = $oStdProgramacao->descricao;
      $this->iSituacao    = $oStdProgramacao->situacao;
    }
  }

  /**
   * @throws DBException
   * @throws ParameterException
   */
  public function salvar() {

    if (empty($this->iCronograma)) {
      throw new ParameterException("Cronograma é de preenchimento obrigatorio.");
    }

    if (empty($this->iInstituicao)) {
      throw new ParameterException("Instituição é de preenchimento obrigatorio.");
    }

    if (empty($this->iAno)) {
      throw new ParameterException("Ano é de preenchimento obrigatorio.");
    }

    if (empty($this->sDescricao)) {
      throw new ParameterException("Descrição é de preenchimento obrigatorio.");
    }

    if (empty($this->iSituacao)) {
      $this->iSituacao = self::SITUACAO_EM_ELABORACAO;
    }

    $oDaoProgramacao = new cl_orcamentoprogramacaofinanceira();
    $oDaoProgramacao->sequencial  = $this->iCodigo;
    $oDaoProgramacao->cronograma  = $this->iCronograma;
    $oDaoProgramacao->instituicao = $this->getInstituicao()->getCodigo();
    $oDaoProgramacao->ano         = $this->iAno;
    $oDaoProgramacao->descricao   = $this->sDescricao;
    $oDaoProgramacao->situacao    = $this->iSituacao;

    if (empty($this->iCodigo)) {

      if (empty($this->aFichas)) {
        throw new BusinessException("Para criação de uma Programação Financeira é necessário informar suas respectivas fichas.");
      }

      $oDaoProgramacao->incluir(null);
      $this->iCodigo = $oDaoProgramacao->sequencial;

      foreach ($this->aFichas as $oFicha) {
        $oFicha->salvar();
      }
    } else {
      $oDaoProgramacao->alterar($this->iCodigo);
    }

    if ($oDaoProgramacao->erro_status == 0) {
      throw new DBException("Erro ao Salvar dados da Programaçao Financeira\n{$oDaoProgramacao->erro_msg}");
    }
  }

  /**
   * @return bool
   * @throws Exception
   * @throws BusinessException
   */
  public function excluir() {

    if ( in_array($this->iSituacao, array(self::SITUACAO_FECHADA, self::SITUACAO_APROVADA)) ) {
      throw new BusinessException("Não é possível excluir uma Programação Financeira aprovada ou fechada.");
    }

    $aFichas = $this->getFichas();
    foreach ($aFichas as $oFicha) {
      $oFicha->excluir();
    }

    $oDaoOrcamentoProgramacaoFinanceira = new cl_orcamentoprogramacaofinanceira();
    $oDaoOrcamentoProgramacaoFinanceira->sequencial = $this->getCodigo();
    $oDaoOrcamentoProgramacaoFinanceira->excluir($this->getCodigo());

    if ($oDaoOrcamentoProgramacaoFinanceira->erro_status == '0') {
      throw new DBException("Não foi possível excluir Programação Financeira.");
    }

    return true;
  }

  /**
   * Busca as fichas financeiras da programação
   * @param null $sWhere
   *
   * @return array|FichaFinanceira[]
   * @throws Exception
   */
  public function getFichas($sWhere = null) {

    if (empty($this->aFichas)) {

      $sCampos   = "fichaorcamentoprogramacaofinanceira.sequencial as sequencial, ";
      $sCampos  .= "ano, orgao, unidade, recurso, anexo, valorindisponivel, valoraprogramar, valororcado, mes, valor ";

      $oDaoFichas      = new cl_fichaorcamentoprogramacaofinanceiravalor();

      $sWherePadrao = "orcamentoprogramacaofinanceira = {$this->iCodigo}";
      if (!empty($sWhere)) {
        $sWherePadrao .= " and {$sWhere} ";
      }

      $sSqlBuscaFichas = $oDaoFichas->sql_query(null, $sCampos, "ano, orgao, unidade, recurso, anexo", $sWherePadrao);
      $rsBuscaFichas   = db_query($sSqlBuscaFichas);

      if ($rsBuscaFichas === false) {
        throw new Exception("Erro ao buscar Fichas da Programação Financeira.");
      }

      $oFicha   = null;
      $aFichas  = array();
      $aValores = array();
      $iCodigoAnterior = 0;
      for ($i = 0; $i < pg_num_rows($rsBuscaFichas); $i++) {

        $oStdFicha  = db_utils::fieldsMemory($rsBuscaFichas, $i);
        $lNovaFicha = $oStdFicha->sequencial != $iCodigoAnterior;

        if ($lNovaFicha) {

          if (!is_null($oFicha)) {

            $oFicha->setValores($aValores);
            $aValores  = array();
          }
          $oFicha = new FichaFinanceira();
          $oFicha->setCodigo($oStdFicha->sequencial);
          $oFicha->setAno($oStdFicha->ano);
          $oFicha->setCodigoOrgao($oStdFicha->orgao);
          $oFicha->setCodigoUnidade($oStdFicha->unidade);
          $oFicha->setCodigoRecurso($oStdFicha->recurso);
          $oFicha->setCodigoAnexo($oStdFicha->anexo);
          $oFicha->setValorIndisponivel((float) $oStdFicha->valorindisponivel);
          $oFicha->setValorProgramar((float) $oStdFicha->valoraprogramar);
          $oFicha->setValorOrcado((float) $oStdFicha->valororcado);

          $aFichas[] = $oFicha;
        }

        $oFichaValor = new FichaFinanceiraValor();
        $oFichaValor->setMes($oStdFicha->mes);
        $oFichaValor->setValor((float) $oStdFicha->valor);

        $aValores[] = $oFichaValor;
        $iCodigoAnterior = $oStdFicha->sequencial;
      }

      if (!is_null($oFicha)) {
        $oFicha->setValores($aValores);
      }

      $this->aFichas = $aFichas;
    }
    return $this->aFichas;
  }

  /**
   * @param cronogramaFinanceiro $OCronograma
   */
  public function setCronograma(cronogramaFinanceiro $oCronograma) {

    $this->oCronograma = $oCronograma;
    $this->iCronograma = $oCronograma->getPerspectiva();
  }

  /**
   * @param Instituicao $oInstituicao
   * @throws ParameterException
   */
  public function setInstituicao(Instituicao $oInstituicao) {

    $this->oInstituicao = $oInstituicao;
    $this->iInstituicao = $oInstituicao->getCodigo();

    if (empty($this->iInstituicao)) {
      throw new ParameterException("Objeto Instituição não foi informado.");
    }
  }

  /**
   * @param int $iAno
   */
  public function setAno($iAno) {
    $this->iAno = $iAno;
  }

  /**
   * @param string $sDescricao
   */
  public function setDescricao($sDescricao) {
    $this->sDescricao = $sDescricao;
  }

  /**
   * @param int $iSituacao
   */
  public function setSituacao($iSituacao) {
    $this->iSituacao = $iSituacao;
  }

  /**
   * @return int|null
   */
  public function getCodigo() {
    return $this->iCodigo;
  }

  /**
   * @return cronogramaFinanceiro
   */
  public function getCronograma() {

    if (empty($this->oCronograma) && !empty($this->iCronograma)) {
      $this->oCronograma = new cronogramaFinanceiro($this->iCronograma);
    }
    return $this->oCronograma;
  }

  /**
   * @return int
   */
  public function getAno() {
    return $this->iAno;
  }

  /**
   * @return int
   */
  public function getSituacao() {
    return $this->iSituacao;
  }

  /**
   * @return string
   */
  public function getDescricao() {
    return $this->sDescricao;
  }

  /**
   * @return Instituicao
   */
  public function getInstituicao() {

    if (empty($this->oInstituicao) && !empty($this->iInstituicao)) {
      $this->oInstituicao = InstituicaoRepository::getInstituicaoByCodigo($this->iInstituicao);
    }

    return $this->oInstituicao;
  }
  /**
   * @param FichaFinanceira[] $aFichas
   */
  public function setFichas($aFichas) {
    $this->aFichas = $aFichas;
  }

  /**
   * Verifica se já existe uma Programação Financeira para o ano e instituição informados.
   *
   * @param Instituicao $oInstituicao
   * @param int         $iAno
   * @param bool        $lHomologado
   *
   * @return bool
   * @throws DBException
   * @throws ParameterException
   */
  public static function possuiProgramacaoInstituicaoAno(Instituicao $oInstituicao, $iAno, $lHomologado = false) {

    if (is_null($oInstituicao)) {
      throw new ParameterException("Parâmetro Instituição obrigatório não informado.");
    }

    if (empty($iAno) || !is_numeric($iAno)) {
      throw new ParameterException("Parâmetro Ano obrigatório não informado.");
    }

    $sWhere = " instituicao = " . $oInstituicao->getCodigo() . " and ano = {$iAno} ";

    if ($lHomologado) {
      $sWhere .= " and situacao = " . ProgramacaoFinanceira::SITUACAO_APROVADA;
    }

    $oDaoProgramacao = new cl_orcamentoprogramacaofinanceira();
    $sSqlProgramacao = $oDaoProgramacao->sql_query(null, "*", null, $sWhere);
    $rsProgramacao   = db_query($sSqlProgramacao);

    if ($rsProgramacao === false) {
      throw  new DBException("Houve um erro ao Buscar a Programação Financeira: {$oDaoProgramacao->erro_msg}");
    }

    return pg_num_rows($rsProgramacao) > 0;
  }

  /**
   * @param $iSituacao
   * @return bool
   * @throws BusinessException
   * @throws DBException
   * @throws ParameterException
   */
  public function alterarSituacao($iSituacao) {

    $aSituacoes = array(self::SITUACAO_EM_ELABORACAO, self::SITUACAO_FECHADA, self::SITUACAO_APROVADA);
    if ( !in_array($iSituacao, $aSituacoes)) {
      throw new ParameterException("Situação {$iSituacao} inválida.");
    }

    if ($this->iSituacao == self::SITUACAO_APROVADA) {
      throw new BusinessException("A Programação Financeira encontra-se APROVADA. Não é possível alterar a situação.");
    }

    if ($this->iSituacao == self::SITUACAO_EM_ELABORACAO && $iSituacao == self::SITUACAO_APROVADA) {

      $sErro  = "A Programação Financeira encontra-se EM ELABORAÇÃO. ";
      $sErro .= "Somente uma Programação Financeira FECHADA pode ser APROVADA.";
      throw new BusinessException($sErro);
    }

    if ($iSituacao == self::SITUACAO_FECHADA) {
      $this->processarFechamento();
    }

    $this->iSituacao = $iSituacao;
    $this->salvar();
    return true;
  }

  /**
   * Verifica o Fechamento da Programação Financeira, batendo os valores totais programados com o valor orçado para cada
   * ficha financeira.
   * @throws BusinessException
   * @throws Exception
   */
  private function processarFechamento() {

    foreach ($this->getFichas() as $oFicha) {

      $nValorOrcado = $oFicha->getValorOrcado();
      $nValorTotal  = $oFicha->getValorIndisponivel() + $oFicha->getValorProgramar();

      foreach ($oFicha->getValores() as $oValorMes) {
        $nValorTotal += $oValorMes->getValor();
      }

      if ($nValorTotal != $nValorOrcado) {

        $sDescricaoFicha = $oFicha->getDescricao();
        $sErro  = "A Programação Financeira não pode ser fechada pois o valor orçado não é igual ao valor total ";
        $sErro .= " para a ficha {$sDescricaoFicha}.";
        throw new BusinessException($sErro);
      }
    }
  }
}