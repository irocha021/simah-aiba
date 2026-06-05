#%%
#import matplotlib
#matplotlib.use('Agg')
#import matplotlib.pyplot as plt
import pandas as pd
import numpy as np
from datetime import datetime

#%%
def processar_vazao_csv(nome_arquivo, mes_interesse, alfa_pond, q_noventa, vsup):
    # Carrega e prepara os dados
    dado = pd.read_csv(nome_arquivo, sep=';', encoding='latin-1')
    dado.rename(columns={
        'Data': 'data',
        'Hora': 'hora',
        'Chuva (mm)': 'chuva',
        'Nivel (cm)': 'nivel',
        'Vazao (m3/s)': 'vazao'
    }, inplace=True)
    dado = dado[['data', 'hora', 'chuva', 'nivel', 'vazao']].dropna()
    dado['data'] = pd.to_datetime(dado['data'])
    dado['dia_ano'] = dado['data'].dt.dayofyear
    dado['mes'] = dado['data'].dt.month
    dado['ano'] = dado['data'].dt.year

    # Agrupa por dia do ano e calcula médias (considerando ano)
    grupo = dado.select_dtypes(include=[np.number]).copy()
    grupo['mes'] = dado['mes']
    grupo['dia_ano'] = dado['dia_ano']
    grupo['ano'] = dado['ano']
    dado_medio = grupo.groupby(['ano', 'dia_ano']).mean().reset_index()

    # Pega ano de referência: primeiro com dados para o mês solicitado
    filtros = dado_medio[dado_medio['mes'] == mes_interesse]
    if filtros.empty:
        raise ValueError(f"Não há dados para o mês {mes_interesse} disponíveis.")
    ano_ref = int(filtros['ano'].iloc[0])
    dados_ano_mes = dado_medio[(dado_medio['ano'] == ano_ref) & (dado_medio['mes'] == mes_interesse)]

    # Seleciona dia de menor vazão (do mês informado)
    idx_min_vazao = dados_ano_mes['vazao'].idxmin()
    dia_inicio = int(dados_ano_mes.loc[idx_min_vazao, 'dia_ano'])
    vazao_inicial = dados_ano_mes.loc[idx_min_vazao, 'vazao']
    print("Vazão mínima para o mês:",vazao_inicial)

    # Calcula corretamente o dia do ano de 31 de outubro para o ano referido
    data_outubro = datetime(ano_ref, 10, 31)  # outubro, não novembro!
    dia_outubro = data_outubro.timetuple().tm_yday

    if dia_inicio > dia_outubro:
        raise ValueError(f"O dia de início ({dia_inicio}) é posterior a 1 de outubro ({dia_outubro}).")

    # Gera intervalo de dias até 31 de outubro (inclusive)
    dias = np.arange(dia_inicio, dia_outubro + 1)  # inclui o último

    # Calcula a curva prevista de vazão
    vazao_prevista = vazao_inicial * np.exp(alfa_pond * (dias - dia_inicio))
    print(f"Vazão prevista é: {vazao_prevista[-1]}, previsão inciada dia: {dia_inicio}")
    # Dados para plotagem
    # dados_ano = dado_medio[dado_medio['ano'] == ano_ref]
    # x = dados_ano['dia_ano']
    # y = dados_ano['vazao']

    # fig, ax = plt.subplots()
    # ax.scatter(x, y, label='Vazão medida', s=8.5)
    # ax.plot(dias, vazao_prevista, label='Vazão prevista (m³/s)', linewidth=2, color='y')
    # ax.axhline(y=q_noventa, label='Q90 (m³/s)', linestyle='--', linewidth=1.3, color='black')
    # ax.axhline(y=vsup, label='Vazão Superficial Outorgada (m³/s)', linestyle='--', linewidth=1.3, color='green')

    # ax.set_xlabel('Dias do ano')
    # ax.set_ylabel('Vazão do rio (m³/s)')
    # lista_mes = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    # ax.set_xticks([31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365])
    # ax.set_xticklabels(lista_mes, rotation=0)
    # ax.set_yticks([0, 30, 60, 90, 120, 150])

    # ax.legend(loc='upper center', bbox_to_anchor=(0.5, -0.2))
    # plt.tight_layout()
    # plt.savefig(nome_figura, dpi=300, bbox_inches='tight', pad_inches=0.5)
    # plt.show()

# Exemplo de uso
processar_vazao_csv(
    nome_arquivo='/root/studio1/mhb-web/storage/app/data/COLONIADOFORMOSO_45880000_so_abril.csv',
    mes_interesse=4,
    alfa_pond=-0.00116262,
    q_noventa=55.36539,
    vsup=17.4,
)


# %%

# %%
