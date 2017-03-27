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

require_once('estilos/grid.style.css');
require_once('scripts/AjaxRequest.js');
require_once('scripts/widgets/windowAux.widget.js');
require_once('scripts/widgets/dbmessageBoard.widget.js');

ViewFichaFinanceira = function(sNomeInstancia) {

  this.sNomeInstancia      = sNomeInstancia;
  this.iCodigo             = null;
  this.oWindowAux          = null;
  this.oGridValoresMensais = null;
  this.oDadosFicha = {
    sDescricao         : null,
    iOrgao             : null,
    iUnidade           : null,
    iRecurso           : null,
    iAnexo             : null,
    nValorOrcado       : null,
    nValorTotal        : null,
    nValorIndisponivel : null,
    nValorProgramar    : null,
    aValoresMensais    : []
  };
  this.fnCallback = null;

  const URL_RPC = 'orc4_programacaofinanceira.RPC.php';

  this.buildContainer = function() {

    var self = this;
    var oDivContainer = document.createElement('div');
    oDivContainer.className = 'container';
    oDivContainer.style.width = '98%';

    var oFieldset = document.createElement('fieldset');
    oFieldset.setAttribute('class', 'separator');

    var oLegend = document.createElement('legend');
    oLegend.setAttribute('class', 'bold');
    oLegend.innerHTML = "Valores Mensais";

    var oInputValorOrcado = document.createElement('input');
    oInputValorOrcado.setAttribute('id', 'valorOrcado');
    oInputValorOrcado.setAttribute('readonly', 'readonly');
    oInputValorOrcado.setAttribute('class', 'readonly');
    oInputValorOrcado.setAttribute('value', js_formatar(this.oDadosFicha.nValorOrcado, 'f'));

    var oLabelValorOrcado = document.createElement('label');
    oLabelValorOrcado.setAttribute('title', 'Valor Orçado');
    oLabelValorOrcado.innerHTML = 'Valor Orçado: ';

    var oInputValorTotal = document.createElement('input');
    oInputValorTotal.setAttribute('id', 'valorTotal');
    oInputValorTotal.setAttribute('readonly', 'readonly');
    oInputValorTotal.setAttribute('class', 'readonly');
    oInputValorTotal.setAttribute('value', js_formatar(this.oDadosFicha.nValorTotal, 'f'));

    var oLabelValorTotal = document.createElement('label');
    oLabelValorTotal.setAttribute('title', 'Valor Total');
    oLabelValorTotal.innerHTML = 'Valor Total: ';

    var oInputIndisponivel = document.createElement('input');
    oInputIndisponivel.setAttribute('id', 'valorIndisponivel');
    oInputIndisponivel.setAttribute('value', js_formatar(this.oDadosFicha.nValorIndisponivel, 'f'));

    var oLabelIndisponivel = document.createElement('label');
    oLabelIndisponivel.setAttribute('title', 'Indisponível');
    oLabelIndisponivel.innerHTML = 'Indisponível: ';

    var oInputProgramar = document.createElement('input');
    oInputProgramar.setAttribute('id', 'valorProgramar');
    oInputProgramar.setAttribute('value', js_formatar(this.oDadosFicha.nValorProgramar, 'f'));

    var oLabelProgramar = document.createElement('label');
    oLabelProgramar.setAttribute('title', 'A Programar');
    oLabelProgramar.innerHTML = 'A Programar: ';

    var oDivGrid = document.createElement('div');
    oDivGrid.id = 'divGridValoresMensais';

    var oBotaoSalvar = document.createElement('input');
    oBotaoSalvar.setAttribute('type', 'button');
    oBotaoSalvar.setAttribute('value', 'Salvar');
    oBotaoSalvar.setAttribute('style', 'margin-top: 15px;');
    oBotaoSalvar.setAttribute('onclick', sNomeInstancia + '.salvar()');

    var oDivSalvar = document.createElement('div');
    oDivSalvar.appendChild(oBotaoSalvar);

    this.oGridValoresMensais = new DBGrid('valoresMensais');
    this.oGridValoresMensais.sNameInstance = this.sNomeInstancia + '.oGridValoresMensais';
    this.oGridValoresMensais.setHeader(['Mês', 'Valor']);
    this.oGridValoresMensais.setCellAlign(['center', 'right']);
    this.oGridValoresMensais.setCellWidth(['50%', '50%']);
    this.oGridValoresMensais.setHeight(250);

    oFieldset.appendChild(oLegend);
    oFieldset.appendChild(oDivGrid);

    var aCamposTabela = [
      { label: oLabelValorOrcado, input: oInputValorOrcado },
      { label: oLabelValorTotal, input: oInputValorTotal },
      { label: oLabelIndisponivel, input: oInputIndisponivel },
      { label: oLabelProgramar, input: oInputProgramar }
    ];

    var oTabela = document.createElement('table');
    oTabela.setAttribute('style', 'width: 100%;');
    for (var iIndice = 0; iIndice < aCamposTabela.length; iIndice++) {

      var oLabel = aCamposTabela[iIndice].label;
      var oInput = aCamposTabela[iIndice].input;

      if (iIndice % 2 == 0) {
        var oLinhaTabela = document.createElement('tr');
      }

      oLabel.setAttribute('class', 'bold');
      oLabel.setAttribute('for', oInput.getAttribute('id'));
      oInput.setAttribute('type', 'text');''
      oInput.setAttribute('style', 'text-align: right;');
      if (oInput.getAttribute('id') != 'valorTotal' && oInput.getAttribute('id') != 'valorOrcado') {

        $(oInput).observe('focus', function(){
          this.value = js_strToFloat(this.value);
        });

        $(oInput).observe('blur', function(){

          this.value = js_formatar(this.value, "f");
          self.calcularValorTotal();
        });

        $(oInput).observe('input', function(){

          var sLabel = $$('label[for="' + this.id + '"]').first().readAttribute('title');
          js_ValidaCampos(this, 4, 'Campo ' + sLabel);
          this.value = this.value.replace(/[^0-9\.\,]/g, "")
        });
      }

      var oColunaLabel = document.createElement('td');
      oColunaLabel.appendChild(oLabel);

      var oColunaInput = document.createElement('td');
      oColunaInput.appendChild(oInput);

      oLinhaTabela.appendChild(oColunaLabel);
      oLinhaTabela.appendChild(oColunaInput);
      oTabela.appendChild(oLinhaTabela);
    }

    oDivContainer.appendChild(oTabela);
    oDivContainer.appendChild(oFieldset);
    oDivContainer.appendChild(oDivSalvar);

    var sTituloJanela = 'Manutenção de Programação Financeira';
    var sTitulo       = 'Ficha Financeira';
    var sHelp         = this.oDadosFicha.sDescricao.urlDecode();

    this.oWindowAux = new windowAux('oWindowAux', sTituloJanela, 550, 550);
    this.oWindowAux.allowDrag(false);
    this.oWindowAux.setContent(oDivContainer);
    this.oWindowAux.setShutDownFunction(function() {

      self.oWindowAux.destroy();
      self.oWindowAux = null;
      self.oDadosFicha = null;
      self.iCodigo = null;
      if (self.fnCallback != null) {
        self.fnCallback();
      }
    });

    var oMessageBoard = new DBMessageBoard('oMessageBoard', sTitulo, sHelp, this.oWindowAux.getContentContainer());
    oMessageBoard.show();

    this.oWindowAux.show();
    this.oGridValoresMensais.show(oDivGrid);
    this.oGridValoresMensais.clearAll(true);

    for (iIndice = 0; iIndice < this.oDadosFicha.aValoresMensais.length; iIndice++) {

      var oCampo = document.createElement('input');
      var sLabel = this.oDadosFicha.aValoresMensais[iIndice].mes.urlDecode();

      oCampo.setAttribute('name',    this.sNomeInstancia + 'ValorMensal' + iIndice);
      oCampo.setAttribute('class',   this.sNomeInstancia + 'ValorMensal');
      oCampo.setAttribute('value',   js_formatar(this.oDadosFicha.aValoresMensais[iIndice].valor, 'f'));
      oCampo.setAttribute('type',    'text');
      oCampo.setAttribute('style',   'text-align: right; width: 100%;');
      oCampo.setAttribute('onfocus', 'this.value = js_strToFloat(this.value)');
      oCampo.setAttribute('onblur', 'this.value = js_formatar(this.value, "f");' + sNomeInstancia + '.calcularValorTotal()');
      oCampo.setAttribute('oninput', 'js_ValidaCampos(this, 4, "Campo ' + sLabel + '"); this.value = this.value.replace(/[^0-9\.,]/g, "")');

      var aLinha = [
        this.oDadosFicha.aValoresMensais[iIndice].mes.urlDecode(),
        oCampo.outerHTML
      ];
      this.oGridValoresMensais.addRow(aLinha);
    }

    this.oGridValoresMensais.renderRows();
  };

  this.calcularValorTotal = function() {

    var nValorTotal        = 0;
    var nValorIndisponivel = js_strToFloat($F('valorIndisponivel'));
    var nValorProgramar    = js_strToFloat($F('valorProgramar'));
    $$('.' + this.sNomeInstancia + 'ValorMensal').each(function(oLinha) {

      var nValor = js_strToFloat(oLinha.getValue());
      nValorTotal += nValor;
    });

    nValorTotal = nValorTotal + nValorIndisponivel + nValorProgramar;
    $('valorTotal').value = js_formatar(nValorTotal, 'f', 2);
  };

  this.salvar = function() {

    var aMeses = [];
    $$('.' + this.sNomeInstancia + 'ValorMensal').each(function(oLinha) {
      aMeses.push(js_strToFloat(oLinha.getValue()));
    });

    var oParametros = {
      'exec' : 'alterarFicha',
      'iCodigoFicha' : this.iCodigo,
      'nValorIndisponivel' : js_strToFloat($F('valorIndisponivel')),
      'nValorProgramar' : js_strToFloat($F('valorProgramar')),
      'aValoresMensais' : aMeses
    };

    new AjaxRequest(URL_RPC, oParametros, function(oRetorno, lErro) {

      if (lErro) {

        alert(oRetorno.mensagem.urlDecode());
        return;
      }

      alert('Ficha alterada com sucesso.');
    }).setMessage('Aguarde, salvando dados da ficha...')
      .asynchronous(false)
      .execute();
  };

  this.carregarDados = function() {

    var lResultado  = true;
    var oParametros = {
      'exec'    : 'getDetalhesFicha',
      'codigo'  : this.iCodigo
    };
    var self = this;

    new AjaxRequest(URL_RPC, oParametros, function(oRetorno, lErro) {

      if (lErro) {

        alert(oRetorno.mensagem.urlDecode());
        lResultado = false;
        return;
      }

      self.oDadosFicha = {
        sDescricao         : oRetorno.descricao,
        iOrgao             : oRetorno.orgao,
        iUnidade           : oRetorno.unidade,
        iRecurso           : oRetorno.recurso,
        iAnexo             : oRetorno.anexo,
        nValorOrcado       : oRetorno.valor_orcado,
        nValorTotal        : oRetorno.valor_total,
        nValorIndisponivel : oRetorno.valor_indisponivel,
        nValorProgramar    : oRetorno.valor_programar,
        aValoresMensais    : oRetorno.meses
      };

    }).setMessage('Aguarde, carregando dados da ficha...')
      .asynchronous(false)
      .execute();

    return lResultado;
  };

  this.show = function(iCodigoFicha) {

    this.iCodigo = iCodigoFicha;

    if (this.oWindowAux === null) {

      if (this.carregarDados()) {
        this.buildContainer();
      }
    }
  };

  /**
   * Funcao de callback quando a janela for fechada
   * @param fnCallback
   */
  this.setCallBackFunction = function(fnCallback) {
    this.fnCallback = fnCallback;
  };
};
