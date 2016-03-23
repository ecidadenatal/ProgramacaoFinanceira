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

class DocumentoResolucaoInteradministrativa {

  /**
   *
   * @var ResolucaoInteradministrativa
   */
  private $oResolucao;

  /**
   *
   * @var PDFDocument
   */
  private $oPdf;

  /**
   *
   * @var integer
   */
  private $iAltura;

  /**
   *
   * @var integer
   */
  private $iLargura;

  public function __construct(ResolucaoInteradministrativa $oResolucao) {

    $this->oPdf       = new PDFDocument;
    $this->iAltura    = 4;
    $this->iLargura   = $this->oPdf->getAvailWidth() - 10;
    $this->oResolucao = $oResolucao;
  }

  public function emitir() {

    $iCodigo = $this->oResolucao->getCodigo();
    if (empty($iCodigo)) {
      throw new ParameterException('Não foi possível emitir o documento.');
    }

    $aSituacoes = ResolucaoInteradministrativa::getSituacoes();
    $aTipos     = ResolucaoInteradministrativa::getTiposAprovacao();
    $sSituacao  = $aSituacoes[$this->oResolucao->getSituacao()];
    $aNaturezas = $this->oResolucao->getNaturezas();
    $sAprovacao = " (Durante reunião do CDM)";
    if ($this->oResolucao->getTipoAprovacao() === ResolucaoInteradministrativa::APROVACAO_ANTES_REUNIAO) {
      $sAprovacao = " (Antes da reunião do CDM)";
    }

    $this->oPdf->Open();
    $this->oPdf->disableFooterDefault();

    /**
     * Cabeçalho
     */
    $this->oPdf->addHeaderDescription("");
    $this->oPdf->addHeaderDescription("");
    $this->oPdf->addHeaderDescription("Número: {$iCodigo}");
    $this->oPdf->addHeaderDescription("Processo: {$this->oResolucao->getProcesso()}");
    $this->oPdf->addHeaderDescription("Situação: {$sSituacao}");

    $this->oPdf->AddPage();
    $this->oPdf->setFontSize(12);
    $this->oPdf->Cell($this->iLargura, $this->iAltura, 'Resolução Inter-Administrativa', 0, 1, 'C');
    $this->oPdf->setFontSize(7);

    /**
     * Dados da RI
     */
    $this->oPdf->ln(5);
    $this->oPdf->setBold(true);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Número:', 0, 0);
    $this->oPdf->setBold(false);
    $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, "{$this->oResolucao->getNumero()}/{$this->oResolucao->getAno()}", 0, 1);

    $this->oPdf->setBold(true);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Processo:', 0, 0);
    $this->oPdf->setBold(false);
    $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, $this->oResolucao->getProcesso(), 0, 1);

    $this->oPdf->setBold(true);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Situação:', 0, 0);
    $this->oPdf->setBold(false);

    $this->oPdf->Cell($this->iLargura * 0.90, $this->iAltura, $sSituacao . $sAprovacao, 0, 1);

    $this->oPdf->setBold(true);
    $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, 'Objetivo:', 0, 0);
    $this->oPdf->setBold(false);
    $this->oPdf->MultiCell($this->iLargura * 0.90, $this->iAltura, $this->oResolucao->getObjetivo());

    /**
     * Cabeçalho fichas
     */
    $this->oPdf->ln(5);
    $oPdf     = $this->oPdf;
    $iLargura = $this->iLargura;
    $iAltura  = $this->iAltura;
    $fCabecalho = function() use ($oPdf, $iAltura, $iLargura) {

      $oPdf->setBold(true);
      $oPdf->Cell($iLargura * 0.60, $iAltura, 'Ficha', 'TBR', 0, 'C');
      $oPdf->Cell($iLargura * 0.15, $iAltura, 'Mês', 'TBR', 0, 'C');
      $oPdf->Cell($iLargura * 0.10, $iAltura, 'Natureza', 'TBR', 0, 'C');
      $oPdf->Cell($iLargura * 0.15, $iAltura, 'Valor', 'TB', 1, 'C');
      $oPdf->setBold(false);
    };
    $this->oPdf->setHeader($fCabecalho);
    /**
     * Fichas
     */
    $fCabecalho();
    foreach ($this->oResolucao->getFichas() as $oFicha) {

      $oFichaFinanceira = $oFicha->getFichaFinanceira();
      $iOrgao           = $oFichaFinanceira->getOrgao()->getCodigoOrgao();
      $iUnidade         = $oFichaFinanceira->getUnidade()->getCodigoUnidade();
      $iAnexo           = $oFichaFinanceira->getAnexo()->getCodigo();
      $iRecurso         = $oFichaFinanceira->getRecurso()->getCodigoRecurso();
      $sFicha           = substr($oFichaFinanceira->getDescricao(), 0, 80);
      $iMes             = $oFicha->getMes();
      $sMes             = empty($iMes) ? '-' : DBDate::getMesExtenso($oFicha->getMes());
      $sNatureza        = $aNaturezas[$oFicha->getNatureza()];
      $sFichaValor      = db_formatar($oFicha->getValor(), 'f');

      $this->oPdf->Cell($this->iLargura * 0.60, $this->iAltura, $sFicha,      'BR', 0, 'L');
      $this->oPdf->Cell($this->iLargura * 0.15, $this->iAltura, $sMes,        'BR', 0, 'C');
      $this->oPdf->Cell($this->iLargura * 0.10, $this->iAltura, $sNatureza,   'BR', 0, 'C');
      $this->oPdf->Cell($this->iLargura * 0.15, $this->iAltura, $sFichaValor, 'B',  1, 'R');
    }

    $oDataAprovacao = $this->oResolucao->getDataAprovacao();
    $sDataAprovacao = $oDataAprovacao->getDia() . ' de ' . DBDate::getMesExtenso($oDataAprovacao->getMes()) . ' de ' . $oDataAprovacao->getAno();

    $this->oPdf->ln(5);
    $this->oPdf->Cell($this->iLargura, $this->iAltura, 'Registrada em ' . $sDataAprovacao, 0, 0, 'R');
    $this->oPdf->showPDF();
  }

}
