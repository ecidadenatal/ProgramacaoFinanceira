<?php
/**
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
 * Class FichaFinanceiraRepository
 */
class FichaFinanceiraRepository {

  /**
   * @type FichaFinanceiraRepository
   */

  private static $oInstancia;

  /**
   * @type array[int]
   */
  private $aFicha = array();

  private function __construct() {}
  private function __clone() {}

  /**
   * @return FichaFinanceiraRepository
   */
  private function getInstancia() {

    if (empty(self::$oInstancia)) {
      self::$oInstancia = new FichaFinanceiraRepository();
    }
    return self::$oInstancia;
  }

  /**
   * @param $iCodigo
   * @return FichaFinanceira
   * @throws ParameterException
   */
  public static function getPorCodigo($iCodigo) {

    if (empty($iCodigo)) {
      throw new ParameterException("Código informado é inválido.");
    }

    if (!array_key_exists($iCodigo, self::getInstancia()->aFicha)) {
      self::getInstancia()->aFicha[$iCodigo] = new FichaFinanceira($iCodigo);
    }
    return self::getInstancia()->aFicha[$iCodigo];
  }

  /**
   * @param Instituicao $oInstituicao
   * @param integer $iAno
   * @param Orgao $oOrgao
   * @param Unidade $oUnidade
   * @param Recurso $oRecurso
   * @param LocalizadorGastos $oAnexo
   *
   * @return FichaFinanceira
   * @throws Exception
   */
  public static function getInstanciaPorComposicao(Instituicao $oInstituicao, $iAno, Orgao $oOrgao, Unidade $oUnidade, Recurso $oRecurso, LocalizadorGastos $oAnexo) {

    $aWhere = array(
      "fichaorcamentoprogramacaofinanceira.ano     = {$iAno}",
      "fichaorcamentoprogramacaofinanceira.orgao   = {$oOrgao->getCodigoOrgao()}",
      "fichaorcamentoprogramacaofinanceira.unidade = {$oUnidade->getCodigoUnidade()}",
      "fichaorcamentoprogramacaofinanceira.recurso = {$oRecurso->getCodigo()}",
      "fichaorcamentoprogramacaofinanceira.anexo   = {$oAnexo->getCodigo()}",
      "orcamentoprogramacaofinanceira.instituicao  = {$oInstituicao->getCodigo()}",
      "orcamentoprogramacaofinanceira.ano          = {$iAno}"
    );

    $oDaoFichaFinanceira = new cl_fichaorcamentoprogramacaofinanceira();
    $sSqlBuscaFicha      = $oDaoFichaFinanceira->sql_query_programacao('fichaorcamentoprogramacaofinanceira.sequencial', implode(' and ', $aWhere));
    $rsBuscaFicha        = $oDaoFichaFinanceira->sql_record($sSqlBuscaFicha);

    if (!$rsBuscaFicha || $oDaoFichaFinanceira->erro_status == "0" || $oDaoFichaFinanceira->numrows == 0) {
      throw new Exception("Ocorreu um erro ao buscar a Ficha Financeira para a Dotação informada.");
    }
    return self::getPorCodigo(db_utils::fieldsMemory($rsBuscaFicha, 0)->sequencial);
  }

  /**
   * @param Dotacao $oDotacao
   *
   * @return FichaFinanceira
   * @throws Exception
   */
  public static function getInstanciaPorDotacao(Dotacao $oDotacao) {

    $oOrgao   = new Orgao($oDotacao->getOrgao());
    $oUnidade = new Unidade($oDotacao->getAno(), $oDotacao->getOrgao(), $oDotacao->getUnidade());
    $oRecurso = new Recurso($oDotacao->getRecurso());
    $oAnexo   = new LocalizadorGastos($oDotacao->getLocalizador());

    return self::getInstanciaPorComposicao(
      $oDotacao->getInstituicao(),
      $oDotacao->getAno(),
      $oOrgao,
      $oUnidade,
      $oRecurso,
      $oAnexo
    );
  }

}