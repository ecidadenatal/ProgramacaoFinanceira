-- instalação
create table orcamentoprogramacaofinanceira (
  sequencial serial not null,
  cronograma int not null,
  instituicao int not null,
  ano int not null,
  descricao varchar(200) not null,
  situacao int not null,
constraint orcamentoprogramacaofinanceira_sequencial_pk primary key (sequencial));
create unique index orcamentoprogramacaofinanceira_ano_instituicao_in on orcamentoprogramacaofinanceira(ano, instituicao);

create table fichaorcamentoprogramacaofinanceira (
  sequencial serial not null,
  orcamentoprogramacaofinanceira int not null references orcamentoprogramacaofinanceira(sequencial),
  orgao int not null,
  ano int not null,
  unidade int not null,
  recurso int not null,
  anexo int not null,
  valororcado numeric default 0,
  valorindisponivel numeric default 0,
  valoraprogramar numeric default 0,
constraint fichaorcamentoprogramacaofinanceira_sequencial_pk primary key (sequencial));

create table fichaorcamentoprogramacaofinanceiravalor (
  sequencial serial not null,
  fichaorcamentoprogramacaofinanceira int not null references fichaorcamentoprogramacaofinanceira(sequencial),
  mes int not null,
  valor numeric default 0,
constraint fichaorcamentoprogramacaofinanceiravalor_sequencial_pk primary key (sequencial));
