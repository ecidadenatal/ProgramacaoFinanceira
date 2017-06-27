create table programacaofinanceirafechamentomensal(
  sequencial serial primary key,
  orcamentoprogramacaofinanceira int not null references orcamentoprogramacaofinanceira(sequencial),
  mes int not null
);

create unique index orcamentoprogramacaofinanceira_mes_un on programacaofinanceirafechamentomensal(orcamentoprogramacaofinanceira, mes);