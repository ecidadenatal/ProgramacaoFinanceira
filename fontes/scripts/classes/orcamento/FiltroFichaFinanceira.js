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

require_once('scripts/widgets/DBAncora.widget.js');
require_once('scripts/widgets/dbtextField.widget.js');

(function(exports) {

	var oAncora = {
		programacao : {
			ancora : new DBAncora('Programação Financeira:', '', true),
			codigo : new DBTextField('codigo_programacao', '', '', 6),
			descricao : new DBTextField('descricao_programacao', '', '', 30),
			abertura : function(lMostra) {

				var sParametros = "funcao_js=parent.oRetornoFunc.programacao.preenche|0|4";

				if (!lMostra) {
					sParametros = "pesquisa_chave=" + oAncora.programacao.codigo.getValue() + "&funcao_js=parent.oRetornoFunc.programacao.completa";
				}

		    js_OpenJanelaIframe(
		    	'CurrentWindow.corpo',
		    	'db_iframe_orcamentoprogramacaofinanceira',
		    	"func_orcamentoprogramacaofinanceira.php?" + sParametros,
	 				'Pesquisa de Programação Financeira',
	 				lMostra
	 			);
			}
		},

		orgao : {
			ancora : new DBAncora('Órgão:', ''. true),
			codigo : new DBTextField('codigo_orgao', '', '', 6),
			descricao : new DBTextField('descricao_orgao', '', '', 30),
			abertura : function(lMostra) {

				var sParametros = "funcao_js=parent.oRetornoFunc.orgao.preenche|0|2";

				if (!lMostra) {
					sParametros = "pesquisa_chave=" + oAncora.orgao.codigo.getValue() + "&funcao_js=parent.oRetornoFunc.orgao.completa";
				}

		    js_OpenJanelaIframe(
		    	'CurrentWindow.corpo',
		    	'db_iframe_orcorgao',
		    	"func_orcorgao.php?" + sParametros,
	 				'Pesquisa de Órgão',
	 				lMostra
	 			);
			}
		},

		unidade : {
			ancora : new DBAncora('Unidade:', ''. true),
			codigo : new DBTextField('codigo_unidade', '', '', 6),
			descricao : new DBTextField('descricao_unidade', '', '', 30),
			abertura : function(lMostra) {

				var sParametros = "funcao_js=parent.oRetornoFunc.unidade.preenche|2|4";

				if (!lMostra) {
					sParametros = "pesquisa_chave=" + oAncora.unidade.codigo.getValue() + "&funcao_js=parent.oRetornoFunc.unidade.completa";
				}

		    js_OpenJanelaIframe(
		    	'CurrentWindow.corpo',
		    	'db_iframe_orcunidade',
		    	"func_orcunidade.php?" + sParametros,
	 				'Pesquisa de Unidade',
	 				lMostra
	 			);
			}
		},

		recurso : {
			ancora : new DBAncora('Recurso:', ''. true),
			codigo : new DBTextField('codigo_recurso', '', '', 6),
			descricao : new DBTextField('descricao_recurso', '', '', 30),
			abertura : function(lMostra) {

				var sParametros = "funcao_js=parent.oRetornoFunc.recurso.preenche|0|1";

				if (!lMostra) {
					sParametros = "pesquisa_chave=" + oAncora.recurso.codigo.getValue() + "&funcao_js=parent.oRetornoFunc.recurso.completa";
				}

		    js_OpenJanelaIframe(
		    	'CurrentWindow.corpo',
		    	'db_iframe_orctiporec',
		    	"func_orctiporec.php?" + sParametros,
	 				'Pesquisa de Recurso',
	 				lMostra
	 			);
			}
		},

		anexo : {
			ancora : new DBAncora('Anexo:', ''. true),
			codigo : new DBTextField('codigo_anexo', '', '', 6),
			descricao : new DBTextField('descricao_anexo', '', '', 30),
			abertura : function(lMostra) {

				var sParametros = "funcao_js=parent.oRetornoFunc.anexo.preenche|0|2";

				if (!lMostra) {
					sParametros = "pesquisa_chave=" + oAncora.anexo.codigo.getValue() + "&funcao_js=parent.oRetornoFunc.anexo.completa";
				}

		    js_OpenJanelaIframe(
		    	'CurrentWindow.corpo',
		    	'db_iframe_ppasubtitulolocalizadorgasto',
		    	"func_ppasubtitulolocalizadorgasto.php?" + sParametros,
	 				'Pesquisa de Anexo',
	 				lMostra
	 			);
			}
		}
	};

	// Programacao
	oAncora.programacao.ancora.onClick(function () {
		oAncora.programacao.abertura(true);
	});

	oAncora.programacao.codigo.addEvent('onInput', "js_ValidaCampos(this, 1, 'Programação')");

	oAncora.programacao.codigo.oHTMLElement.addEventListener("change", function () {
		oAncora.programacao.abertura(false);
	});

	oAncora.programacao.descricao.setReadOnly(true);

	// Orgao
	oAncora.orgao.ancora.onClick(function () {
		oAncora.orgao.abertura(true);
	});

	oAncora.orgao.codigo.addEvent('onInput', "js_ValidaCampos(this, 1, 'Órgão')");

	oAncora.orgao.codigo.oHTMLElement.addEventListener("change", function () {
		oAncora.orgao.abertura(false);
	});

	oAncora.orgao.descricao.setReadOnly(true);

	// Unidade
	oAncora.unidade.ancora.onClick(function () {
		oAncora.unidade.abertura(true);
	});

	oAncora.unidade.codigo.addEvent('onInput', "js_ValidaCampos(this, 1, 'Unidade')");

	oAncora.unidade.codigo.oHTMLElement.addEventListener("change", function () {
		oAncora.unidade.abertura(false);
	});

	oAncora.unidade.descricao.setReadOnly(true);

	// Recurso
	oAncora.recurso.ancora.onClick(function () {
		oAncora.recurso.abertura(true);
	});

	oAncora.recurso.codigo.addEvent('onInput', "js_ValidaCampos(this, 1, 'Recurso')");

	oAncora.recurso.codigo.oHTMLElement.addEventListener("change", function () {
		oAncora.recurso.abertura(false);
	});

	oAncora.recurso.descricao.setReadOnly(true);

	// Anexo
	oAncora.anexo.ancora.onClick(function () {
		oAncora.anexo.abertura(true);
	});

	oAncora.anexo.codigo.addEvent('onInput', "js_ValidaCampos(this, 1, 'Anexo')");

	oAncora.anexo.codigo.oHTMLElement.addEventListener("change", function () {
		oAncora.anexo.abertura(false);
	});

	oAncora.anexo.descricao.setReadOnly(true);

	var FichaFinanceira = function() {

		var lExibeProgramacao = true;

		this.show = function (oElemento) {

			var output = document.createElement("table");

			for (var sCampo in oAncora) {

				if (!lExibeProgramacao && sCampo == 'programacao') {
					continue;
				}

				var trElement = document.createElement('tr'),
				    tdElement = document.createElement('td');

				trElement.appendChild(tdElement);

				output.appendChild(trElement);

				oAncora[sCampo].ancora.show(tdElement);
				tdElement = document.createElement('td');
				oAncora[sCampo].codigo.show(tdElement);
				tdElement.appendChild(document.createTextNode(' '));
				oAncora[sCampo].descricao.show(tdElement, true);
				trElement.appendChild(tdElement);
			}

			oElemento.appendChild(output);
		};

		this.exibeProgramacaoFinanceira = function (lMostra) {
			lExibeProgramacao = lMostra;
		};

		this.getCodigoProgramacao = function () {
			return oAncora.programacao.codigo.getValue();
		};

		this.getCodigoOrgao = function () {
			return oAncora.orgao.codigo.getValue();
		};

		this.getCodigoUnidade = function () {
			return oAncora.unidade.codigo.getValue();
		};

		this.getCodigoRecurso = function () {
			return oAncora.recurso.codigo.getValue();
		};

		this.getCodigoAnexo = function () {
			return oAncora.anexo.codigo.getValue();
		};
	};

	var oRetornoFunc = {
		programacao : {
			preenche : function(iCodigo, sDescricao) {
				oAncora.programacao.codigo.setValue(iCodigo);
				oAncora.programacao.descricao.setValue(sDescricao);
				db_iframe_orcamentoprogramacaofinanceira.hide();
			},
			completa : function(sDescricao, lErro) {
				if (lErro) {
					oAncora.programacao.codigo.setValue("");
				}

				oAncora.programacao.descricao.setValue(sDescricao);
			}
		},

		orgao : {
			preenche : function(iCodigo, sDescricao) {
				oAncora.orgao.codigo.setValue(iCodigo);
				oAncora.orgao.descricao.setValue(sDescricao);
				db_iframe_orcorgao.hide();
			},
			completa : function(sDescricao, lErro) {
				if (lErro) {
					oAncora.orgao.codigo.setValue("");
				}

				oAncora.orgao.descricao.setValue(sDescricao);
			}
		},

		unidade : {
			preenche : function(iCodigo, sDescricao) {
				oAncora.unidade.codigo.setValue(iCodigo);
				oAncora.unidade.descricao.setValue(sDescricao);
				db_iframe_orcunidade.hide();
			},
			completa : function(sDescricao, lErro) {
				if (lErro) {
					oAncora.unidade.codigo.setValue("");
				}

				oAncora.unidade.descricao.setValue(sDescricao);
			}
		},

		recurso : {
			preenche : function(iCodigo, sDescricao) {
				oAncora.recurso.codigo.setValue(iCodigo);
				oAncora.recurso.descricao.setValue(sDescricao);
				db_iframe_orctiporec.hide();
			},
			completa : function(sDescricao, lErro) {
				if (lErro) {
					oAncora.recurso.codigo.setValue("");
				}

				oAncora.recurso.descricao.setValue(sDescricao);
			}
		},

		anexo : {
			preenche : function(iCodigo, sDescricao) {
				oAncora.anexo.codigo.setValue(iCodigo);
				oAncora.anexo.descricao.setValue(sDescricao);
				db_iframe_ppasubtitulolocalizadorgasto.hide();
			},
			completa : function(sDescricao, lErro) {
				if (lErro) {
					oAncora.anexo.codigo.setValue("");
				}

				oAncora.anexo.descricao.setValue(sDescricao);
			}
		}
	};

	exports.oRetornoFunc = oRetornoFunc;
	exports.FiltroFichaFinanceira = FichaFinanceira;
}) (this);
