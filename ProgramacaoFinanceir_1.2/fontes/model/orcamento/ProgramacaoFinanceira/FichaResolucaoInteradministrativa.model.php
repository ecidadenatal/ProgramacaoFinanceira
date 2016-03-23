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
 * Class FichaResolucaoInteradministrativa
 */
class FichaResolucaoInteradministrativa {

  /**
   * @var integer
   */
  private $iCodigo;

  /**
   * @var integer
   */
  private $iMes;

  /**
   * @var integer
   */
  private $nValor;

  /**
   * @var integer
   */
  private $iNatureza;

  /**
   * @var ResolucaoInteradministrativa
   */
  private $oResolucaoInteradministrativa;

  /**
   * @var FichaFinanceira
   */
  private $oFichaFinanceira;

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
  public function getMes() {
    return $this->iMes;
  }

  /**
   * @param integer $iMes
   */
  public function setMes($iMes) {
    $this->iMes = $iMes;
  }

  /**
   * @return mixed
   */
  public function getValor() {
    return $this->nValor;
  }

  /**
   * @param float $nValor
   */
  public function setValor($nValor) {
    $this->nValor = $nValor;
  }

  /**
   * @return integer
   */
  public function getNatureza() {
    return $this->iNatureza;
  }

  /**
   * @param integer $iNatureza
   */
  public function setNatureza($iNatureza) {
    $this->iNatureza = $iNatureza;
  }

  /**
   * @return ResolucaoInteradministrativa
   */
  public function getResolucaoInteradministrativa() {
    return $this->oResolucaoInteradministrativa;
  }

  /**
   * @param ResolucaoInteradministrativa $oResolucaoInteradministrativa
   */
  public function setResolucaoInteradministrativa(ResolucaoInteradministrativa $oResolucaoInteradministrativa) {
    $this->oResolucaoInteradministrativa = $oResolucaoInteradministrativa;
  }

  /**
   * @return FichaFinanceira
   */
  public function getFichaFinanceira() {
    return $this->oFichaFinanceira;
  }

  /**
   * @param FichaFinanceira $oFichaFinanceira
   */
  public function setFichaFinanceira(FichaFinanceira $oFichaFinanceira) {
    $this->oFichaFinanceira = $oFichaFinanceira;
  }
}