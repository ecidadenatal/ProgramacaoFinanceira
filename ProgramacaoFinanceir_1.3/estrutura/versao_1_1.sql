create table resolucaointeradministrativa(
  sequencial serial NOT NULL,
  processo varchar(10),
  numero int not null,
  ano int not null,
  data date not null,
  objetivo text not null,
  situacao int not null default 1,
  constraint resolucaointeradministrativa_sequencial_pk primary key(sequencial)
);

create table ficharesolucaointeradministrativa(
  sequencial serial not null,
  resolucaointeradministrativa int not null references resolucaointeradministrativa(sequencial),
  fichaorcamentoprogramacaofinanceira int not null references fichaorcamentoprogramacaofinanceira(sequencial),
  natureza int not null,
  valor numeric not null,
  mes int,
  constraint ficharesolucaointeradministrativa_sequencial_pk primary key(sequencial)
);

create table autorizacaoresolucaointeradministrativa(
  sequencial                   serial  not null primary key,
  resolucaointeradministrativa integer not null unique references resolucaointeradministrativa(sequencial),
  data                         date not null,
  tipo                         int  not null default 1
);
