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
 * Class ResolucaoInteradministrativa
 */
class ResolucaoInteradministrativa {

  const SITUACAO_EM_ELABORACAO = 1;
  const SITUACAO_FECHADA       = 2;
  const SITUACAO_APROVADA      = 3;

  const NATUREZA_CREDITO       = 1;
  const NATUREZA_REDUCAO       = 2;
  const NATUREZA_OUTRAS_FONTES = 3;
  const NATUREZA_INDISPONIVEL  = 4;

  const APROVACAO_ANTES_REUNIAO = 1;
  const APROVACAO_REUNIAO       = 2;

  /**
   * @var integer
   */
  private $iCodigo;

  /**
   * @var string
   */
  private $sProcesso;

  /**
   * @var integer
   */
  private $iNumero;

  /**
   * @var integer
   */
  private $iAno;

  /**
   * @var string
   */
  private $sObjetivo;

  /**
   * @var integer
   */
  private $iSituacao;

  /**
   * @var array
   */
  private $aFichas;

  /**
   * @var DBDate
   */
  private $oData;

  /**
   * @var integer
   */
  private $iCodigoAprovacao;

  /**
   * @var DBDate
   */
  private $oDataAprovacao;

  /**
   * @var integer
   */
  private $iTipoAprovacao;

  /**
   * @var integer
   */
  private $iResolucaoInterAdministrativa;

  /**
   * Metodo Construtor da Resolu��o Financeira
   *
   * @param integer $iCodigo
   */
  public function __construct($iCodigo = '') {

    $this->iCodigo = $iCodigo;

    if (!empty($iCodigo)) {

      $oDaoResolucao = new cl_resolucaointeradministrativa;
      $aCampos = array(
        'resolucaointeradministrativa.sequencial',
        'resolucaointeradministrativa.processo',
        'resolucaointeradministrativa.numero',
        'resolucaointeradministrativa.ano',
        'resolucaointeradministrativa.data',
        'resolucaointeradministrativa.objetivo',
        'resolucaointeradministrativa.situacao',
        'autorizacaoresolucaointeradministrativa.sequencial as codigo_autorizacao',
        'autorizacaoresolucaointeradministrativa.data as data_aprovacao',
        'autorizacaoresolucaointeradministrativa.tipo as tipo_aprovacao'
      );
      $sSqlBuscaResolucao = $oDaoResolucao->sql_query_autorizacao(implode(',', $aCampos), " resolucaointeradministrativa.sequencial = {$iCodigo} ");
      $rsBuscaResolucao   = $oDaoResolucao->sql_record($sSqlBuscaResolucao);

      if ($rsBuscaResolucao === false) {
        throw new DBException("Erro ao buscar a Resolu��o Inter-Administrativa.");
      }

      if ($oDaoResolucao->numrows != 1) {
        throw new Exception("Resolu��o Inter-Administrativa n�o encontrada.");
      }

      $oStdResolucao = db_utils::fieldsMemory($rsBuscaResolucao, 0);

      $this->sProcesso = $oStdResolucao->processo;
      $this->iNumero   = $oStdResolucao->numero;
      $this->iAno      = $oStdResolucao->ano;
      $this->oData     = new DBDate($oStdResolucao->data);
      $this->sObjetivo = $oStdResolucao->objetivo;
      $this->iSituacao = $oStdResolucao->situacao;

      if (!empty($oStdResolucao->codigo_autorizacao)) {

        $this->iCodigoAprovacao = $oStdResolucao->codigo_autorizacao;
        $this->oDataAprovacao   = new DBDate($oStdResolucao->data_aprovacao);
        $this->iTipoAprovacao   = $oStdResolucao->tipo_aprovacao;
      }
    }
  }

  /**
   * Retorna um array contendo o nome das naturezas e sua respectiva constante como �ndice.
   * @return array
   */
  public static function getNaturezas() {

    return array(
      self::NATUREZA_CREDITO       => "Cr�dito",
      self::NATUREZA_REDUCAO       => "Redu��o",
      self::NATUREZA_OUTRAS_FONTES => "Outras Fontes",
      self::NATUREZA_INDISPONIVEL  => "Indispon�vel"
    );
  }

  /**
   * Retorna um array contendo o nome das situa��es e sua respectiva constante como �ndice.
   * @return array
   */
  public static function getSituacoes() {

    return array(
      self::SITUACAO_EM_ELABORACAO => 'Em elabora��o',
      self::SITUACAO_FECHADA       => 'Fechada',
      self::SITUACAO_APROVADA      => 'Aprovada'
    );
  }

  /**
   * Retorna um array contendo o nome dos tipos de aprova��es e suas respectivas constantes como �ndice.
   * @return array
   */
  public static function getTiposAprovacao() {

    return array(
      self::APROVACAO_ANTES_REUNIAO => 'Antes da Reuni�o',
      self::APROVACAO_REUNIAO       => 'Na Reuni�o'
    );
  }

  /**
   * @return FichaResolucaoInteradministrativa[]
   */
  public function getFichas() {

    if (is_null($this->aFichas)) {

      $oDaoFichaResolucao       = new cl_ficharesolucaointeradministrativa;
      $sSqlBuscaFichasResolucao = $oDaoFichaResolucao->sql_query_file(null, "*", null, " resolucaointeradministrativa = {$this->getCodigo()} ");
      $rsBuscaFichasResolucao   = $oDaoFichaResolucao->sql_record($sSqlBuscaFichasResolucao);

      if ($rsBuscaFichasResolucao === false) {
        return array();
      }

      for ($iIndice = 0; $iIndice < $oDaoFichaResolucao->numrows; $iIndice++) {

        $oStdFicha = db_utils::fieldsMemory($rsBuscaFichasResolucao, $iIndice);

        $oFicha = new FichaResolucaoInteradministrativa;
        $oFicha->setCodigo($oStdFicha->sequencial);
        $oFicha->setMes($oStdFicha->mes);
        $oFicha->setValor($oStdFicha->valor);
        $oFicha->setNatureza($oStdFicha->natureza);
        $oFicha->setResolucaoInteradministrativa($this);
        $oFicha->setFichaFinanceira(
          FichaFinanceiraRepository::getPorCodigo($oStdFicha->fichaorcamentoprogramacaofinanceira)
        );

        $this->adicionarFicha($oFicha);
      }
    }

    return $this->aFichas;
  }

  /**
   * @param FichaResolucaoInteradministrativa $oFicha
   */
  public function adicionarFicha(FichaResolucaoInteradministrativa $oFicha) {
    $this->aFichas[] = $oFicha;
  }

  /**
   * Salva a Resolu��o Inter-Administrativa e o v�nculo com as Fichas
   */
  public function salvar() {

    if (empty($this->iCodigo)) {

      $this->iSituacao = self::SITUACAO_EM_ELABORACAO;
      $this->iNumero   = $this->getProximoNumero();
    } else {

      if ($this->getSituacao() != self::SITUACAO_EM_ELABORACAO) {
        throw new BusinessException("A Resolu��o Inter-Administrativa Fechada ou Aprovada n�o poder� ser alterada.");
      }
    }
    $this->persistir();
    $this->salvarFichas();
  }

  /**
   * Obtem Pr�ximo Numero da Resolu��o
   */
  private function getProximoNumero() {

    $oDaoResolucao     = new cl_resolucaointeradministrativa;
    $sSqlProximoNumero = $oDaoResolucao->sql_query_file(null, " coalesce(max(numero), 0)+1 as numero");
    $rsProximoNumero   = $oDaoResolucao->sql_record($sSqlProximoNumero);

    if ($oDaoResolucao->erro_status == '0') {
      throw new DBException("N�o foi poss�vel obter o pr�ximo n�mero da Resolu��o Inter-Administrativa.");
    }

    return db_utils::fieldsMemory($rsProximoNumero, 0)->numero;
  }

  /**
   * Salva v�nculo das Fichas a Resolu��o Inter-Administrativa
   *
   * @throws DBException
   */
  private function salvarFichas() {

    $this->validarFichas();

    $oDaoFichas = new cl_ficharesolucaointeradministrativa;
    $oDaoFichas->excluir(null, " resolucaointeradministrativa = {$this->getCodigo()} ");

    foreach ($this->getFichas() as $oFicha) {

      $oDaoFichas->sequencial = null;
      $oDaoFichas->mes        = $oFicha->getMes();
      $oDaoFichas->valor      = $oFicha->getValor();
      $oDaoFichas->natureza   = $oFicha->getNatureza();
      $oDaoFichas->resolucaointeradministrativa        = $this->getCodigo();
      $oDaoFichas->fichaorcamentoprogramacaofinanceira = $oFicha->getFichaFinanceira()->getCodigo();
      $oDaoFichas->incluir(null);

      if ($oDaoFichas->erro_status == '0') {
        throw new DBException("Erro ao salvar v�nculo das Fichas a Resolu��o Inter-Administrativa.");
      }
    }
  }

  /**
   * Valida Fichas da Resolu��o Intraadministrativa
   * @return bool
   * @throws BusinessException
   */
  public function validarFichas() {

    $aNaturezas = array(
      self::NATUREZA_CREDITO,
      self::NATUREZA_REDUCAO,
      self::NATUREZA_INDISPONIVEL
    );
    $aRecursos = array();

    foreach ($this->getFichas() as $oFicha) {

      $iCodigoRecurso = $oFicha->getFichaFinanceira()->getRecurso()->getCodigo();

      if (in_array($oFicha->getNatureza(), $aNaturezas)) {
        $aRecursos[$iCodigoRecurso][] = $oFicha;
      }
    }

    $nTotalCreditoRI      = 0;
    $nTotalReducaoRI      = 0;
    $nTotalIndisponivelRI = 0;

    foreach ($aRecursos as $iCodigoRecurso => $aFichas) {

      $nTotalReducao      = 0;
      $nTotalCredito      = 0;
      $nTotalIndisponivel = 0;

      foreach ($aFichas as $oFicha) {

        if ($oFicha->getNatureza() == self::NATUREZA_REDUCAO) {
          $nTotalReducao += $oFicha->getValor();
        }

        if ($oFicha->getNatureza() == self::NATUREZA_CREDITO) {
          $nTotalCredito += $oFicha->getValor();
        }

        if ($oFicha->getNatureza() == self::NATUREZA_INDISPONIVEL) {
          $nTotalIndisponivel += $oFicha->getValor();
        }
      }

      if (round($nTotalCredito, 2) != round($nTotalReducao + $nTotalIndisponivel, 2)) {

        $oRecurso = RecursoRepository::getRecursoPorCodigo($iCodigoRecurso);
        $sMensagem  = "N�o � poss�vel salvar a RI, pois o total de Cr�ditos e de Redu��es do Recurso ";
        $sMensagem .= "{$iCodigoRecurso} - {$oRecurso->getDescricao()} s�o diferentes.";
        throw new BusinessException($sMensagem);
      }

      $nTotalCreditoRI      += $nTotalCredito;
      $nTotalReducaoRI      += $nTotalReducao;
      $nTotalIndisponivelRI += $nTotalIndisponivel;
    }

    if (round($nTotalCreditoRI, 2) != round($nTotalReducaoRI + $nTotalIndisponivelRI, 2)) {
      throw new BusinessException("N�o � poss�vel salvar a RI, pois o total de Cr�ditos e de Redu��es s�o diferentes.");
    }

    return true;
  }

  /**
   * Exclui Resolu��o Inter-Administrativa e as Fichas v�nculadas
   *
   * @throws BusinessException
   */
  public function excluir() {

    if ($this->getSituacao() != self::SITUACAO_EM_ELABORACAO) {
      throw new BusinessException("A Resolu��o Inter-Administrativa Fechada ou Aprovada n�o poder� ser exclu�da.");
    }

    $oDaoFichas = new cl_ficharesolucaointeradministrativa;
    $oDaoFichas->excluir(null, " resolucaointeradministrativa = {$this->getCodigo()} ");

    if ($oDaoFichas->erro_status == '0') {
        throw new DBException("Ocorreu um erro ao excluir as Fichas da Resolu��o Inter-Administrativa.");
    }

    $oDaoResolucao = new cl_resolucaointeradministrativa;
    $oDaoResolucao->excluir(null, " sequencial = {$this->getCodigo()}");

    if ($oDaoResolucao->erro_status == '0') {
        throw new DBException("Ocorreu um erro ao excluir a Resolu��o Inter-Administrativa.");
    }
  }

  /**
   * Persiste as informa��es na DAO
   *
   */
  private function persistir() {

    $oDaoResolucao = new cl_resolucaointeradministrativa;

    $oDaoResolucao->sequencial = $this->getCodigo();
    $oDaoResolucao->ano        = $this->getAno();
    $oDaoResolucao->numero     = $this->getNumero();
    $oDaoResolucao->processo   = pg_escape_string($this->getProcesso());
    $oDaoResolucao->objetivo   = pg_escape_string($this->getObjetivo());
    $oDaoResolucao->situacao   = $this->getSituacao();
    $oDaoResolucao->data       = $this->getData()->getDate();

    if (empty($this->iCodigo)) {
      $oDaoResolucao->incluir(null);
    } else {
      $oDaoResolucao->alterar($this->iCodigo);
    }

    if ($oDaoResolucao->erro_status == '0') {
      throw new DBException("Ocorreu um erro ao salvar os dados da Resolu��o Inter-Administrativa.");
    }

    $this->setCodigo($oDaoResolucao->sequencial);
    $this->setNumero($oDaoResolucao->numero);
  }

  /**
   * Altera Situa��o, Tipo e Data de Aprova��o da Resolu��o Inter-Administrativa
   *
   * @throws BusinessException
   */
  public function alterarSituacao($iSituacao, DBDate $oDataAprovacao = null) {

    if (empty($this->iCodigo)) {
      throw new ParameterException("Resolu��o Inter-Administrativa n�o informada.");
    }

    if ($iSituacao == self::SITUACAO_APROVADA && is_null($oDataAprovacao)) {
      throw new BusinessException("Campo Data Aprova��o � de preenchimento obrigat�rio.");
    }

    if ($iSituacao == self::SITUACAO_APROVADA) {

      if ($oDataAprovacao->getTimestamp() < $this->getData()->getTimeStamp()) {
        throw new BusinessException("Data de aprova��o n�o pode anteceder � cria��o da RI");
      }
    }

    switch ($this->getSituacao()) {

      case self::SITUACAO_APROVADA:

        throw new BusinessException("N�o � poss�vel alterar a situa��o de uma RI j� aprovada.");
        break;

      case self::SITUACAO_FECHADA:

        if ($iSituacao == self::SITUACAO_APROVADA) {

          $this->setDataAprovacao($oDataAprovacao);

          $iTipo = (int) $this->getTipoAprovacao();
          if (empty($iTipo)) {
            throw new BusinessException("Campo Tipo de Aprova��o � de preenchimento obrigat�rio.");
          }
          $this->autorizar();
        }
        break;

      case self::SITUACAO_EM_ELABORACAO:

        if ($iSituacao != self::SITUACAO_FECHADA) {
          throw new BusinessException("N�o � poss�vel alterar para outra situa��o al�m de fechado.");
        }
        break;

      default:
        throw new Exception("Situa��o da Resolu��o Inter-Administrativa Inv�lida.");
    }

    if ($iSituacao == self::SITUACAO_FECHADA) {
      $this->validarSaldosRI();
    }

    $this->setSituacao($iSituacao);
    $this->persistir();
  }

  /**
   * Salva a Situa��o, Tipo e Data de Aprova��o da Resolu��o Inter-Administrativa
   *
   * @throws BusinessException
   */
  public function autorizar() {

    $this->validarSaldosRI();

    $oDaoAutorizacao = new cl_autorizacaoresolucaointeradministrativa;
    $oDaoAutorizacao->sequencial                   = $this->getCodigoAprovacao();
    $oDaoAutorizacao->data                         = $this->getDataAprovacao()->getDate();
    $oDaoAutorizacao->tipo                         = $this->getTipoAprovacao();
    $oDaoAutorizacao->resolucaointeradministrativa = $this->getCodigo();
    $oDaoAutorizacao->incluir(null);

    if ($oDaoAutorizacao->erro_status == '0') {
      throw new DBException("Ocorreu um erro ao salvar os dados da Resolu��o Inter-Administrativa.");
    }

    $this->setCodigoAprovacao($oDaoAutorizacao->sequencial);

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
   * @return integer
   */
  public function getNumero() {
    return $this->iNumero;
  }

  /**
   * @param integer $iNumero
   */
  public function setNumero($iNumero) {
    $this->iNumero = $iNumero;
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
   * @return integer
   */
  public function getProcesso() {
    return $this->sProcesso;
  }

  /**
   * @param string $sProcesso
   */
  public function setProcesso($sProcesso) {
    $this->sProcesso = $sProcesso;
  }

  /**
   * @return string
   */
  public function getObjetivo() {
    return $this->sObjetivo;
  }

  /**
   * @param string $sObjectivo
   */
  public function setObjetivo($sObjetivo) {
    $this->sObjetivo = $sObjetivo;
  }

  /**
   * @return integer
   */
  public function getSituacao() {
    return $this->iSituacao;
  }

  /**
   * @param integer $iSituacao
   * @throws ParameterException
   */
  private function setSituacao($iSituacao) {

    $aSituacoes = array_keys(self::getSituacoes());

    if (!in_array($iSituacao, $aSituacoes)) {
      throw new ParameterException("Situa��o {$iSituacao} inv�lida.");
    }

    $this->iSituacao = $iSituacao;
  }

  /**
   * @return DBDate
   */
  public function getData() {
    return $this->oData;
  }

  /**
   * @param DBDate $oData
   */
  public function setData(DBDate $oData) {
    $this->oData = $oData;
  }

  /**
   * @return integer
   */
  public function getCodigoAprovacao() {
    return $this->iCodigoAprovacao;
  }

  /**
   * @param integer $iCodigoAprovacao
   */
  public function setCodigoAprovacao($iCodigoAprovacao) {
    $this->iCodigoAprovacao = $iCodigoAprovacao;
  }

  /**
   * @return DBDate
   */
  public function getDataAprovacao() {
    return $this->oDataAprovacao;
  }

  /**
   * @param DBDate $oDataAprovacao
   */
  private function setDataAprovacao(DBDate $oDataAprovacao) {
    $this->oDataAprovacao = $oDataAprovacao;
  }

  /**
   * @return integer
   */
  public function getTipoAprovacao() {
    return $this->iTipoAprovacao;
  }

  /**
   * @param integer $iTipoAprovacao
   */
  public function setTipoAprovacao($iTipoAprovacao) {

    $aAprovacoes = array_keys(self::getTiposAprovacao());

    if (!in_array($iTipoAprovacao, $aAprovacoes)) {
      throw new ParameterException("Aprova��o {$iTipoAprovacao} inv�lida.");
    }

    $this->iTipoAprovacao = $iTipoAprovacao;
  }

  /**
   * Retorna o valor dos remanejamentos aprovados para a natureza informada, ficha e m�s.
   *
   * @param FichaFinanceira $oFicha
   * @param int             $iNatureza
   * @param int             $iMes
   *
   * @return float
   * @throws DBException
   * @throws ParameterException
   */
  public static function getRemanejamentoPorNatureza(FichaFinanceira $oFicha, $iNatureza, $iMes = null) {

    $aNaturezas = array_keys(self::getNaturezas());
    if (empty($iNatureza) || !in_array($iNatureza, $aNaturezas)) {
      throw  new ParameterException("Par�metro Natureza inv�lido.");
    }

    if ($iNatureza != self::NATUREZA_INDISPONIVEL && empty($iMes)) {
      throw new ParameterException("Par�metro M�s � obrigat�rio para a Natureza informada.");
    }

    if ($iMes != null && ($iMes < DBDate::JANEIRO || $iMes > DBDate::DEZEMBRO)) {
      throw new ParameterException("Par�metro M�s inv�lido.");
    }

    $sCampos  = " coalesce(sum(ficharesolucaointeradministrativa.valor), 0) as total ";
    $sWhere   = " ficharesolucaointeradministrativa.natureza = {$iNatureza} and ";
    $sWhere  .= " resolucaointeradministrativa.ano = {$oFicha->getAno()} and ";
    $sWhere  .= " ficharesolucaointeradministrativa.fichaorcamentoprogramacaofinanceira  = {$oFicha->getCodigo()} ";

    if ($iNatureza != self::NATUREZA_INDISPONIVEL) {
      $sWhere .= " and ficharesolucaointeradministrativa.mes = {$iMes} ";
    }
    $oDaoRIAutorizadas = new cl_autorizacaoresolucaointeradministrativa();
    $sSqlRIAutorizadas = $oDaoRIAutorizadas->sql_query_autorizadas($sCampos, $sWhere);
    $rsRIAutorizadas   = db_query($sSqlRIAutorizadas);

    if ($rsRIAutorizadas === false || pg_num_rows($rsRIAutorizadas) == 0) {
      throw new DBException("Houve um erro ao calcular o cr�dito para a Ficha Financeira.");
    }
    return db_utils::fieldsMemory($rsRIAutorizadas, 0)->total;
  }

  /**
   * Valida se h� saldo suficiente para as fichas e meses cadastrados na RI.
   * @throws BusinessException
   */
  private function validarSaldosRI() {

    $aFichasAgrupadas = array();
    foreach($this->getFichas() as $oFichaRI) {

      $iCodigoFicha = $oFichaRI->getFichaFinanceira()->getCodigo();
      $iMes         = $oFichaRI->getMes();

      if (!isset($aFichasAgrupadas[$iCodigoFicha])) {

        $oStdFicha = new stdClass();
        $oStdFicha->total_indisponivel = 0;
        $oStdFicha->ficha = $oFichaRI->getFichaFinanceira();
        $oStdFicha->meses = array();
        $aFichasAgrupadas[$iCodigoFicha] = $oStdFicha;
      }

      switch ($oFichaRI->getNatureza()) {

        case self::NATUREZA_INDISPONIVEL:

          $aFichasAgrupadas[$iCodigoFicha]->total_indisponivel += $oFichaRI->getValor();
          break;

        case self::NATUREZA_REDUCAO:

          if (!isset($aFichasAgrupadas[$iCodigoFicha]->meses[$iMes])) {
            $aFichasAgrupadas[$iCodigoFicha]->meses[$iMes] = 0;
          }
          $aFichasAgrupadas[$iCodigoFicha]->meses[$iMes] += $oFichaRI->getValor();
          break;
      }

    }

    foreach ($aFichasAgrupadas as $oFichaFinanceira) {

      if (round($oFichaFinanceira->ficha->getSaldoIndisponivel(), 2) < round($oFichaFinanceira->total_indisponivel, 2)) {

        $sErro  = "N�o � poss�vel aprovar a RI, pois o valor indispon�vel da ficha ";
        $sErro .= $oFichaFinanceira->ficha->getDescricao() . " n�o � o suficiente para a redu��o cadastrada.";
        throw new BusinessException($sErro);
      }

      foreach ($oFichaFinanceira->meses as $iMes => $nValor) {

        $nDisponivelReducao = $oFichaFinanceira->ficha->getSaldoNoMes($iMes)->getValorDisponivelParaEmpenho();
        if (round($nDisponivelReducao, 2) < round($nValor, 2)) {

          $sFicha = $oFichaFinanceira->ficha->getDescricao();
          $sMes   = DBDate::getMesExtenso($iMes);
          $sErro  = "N�o � poss�vel aprovar a RI, pois o saldo da ficha {$sFicha} no m�s de {$sMes} n�o � suficiente ";
          $sErro .= "para a redu��o cadastrada.";
          throw new BusinessException($sErro);
        }
      }
    }
  }
}
