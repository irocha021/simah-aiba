#%%
import pandas as pd
import numpy as np
from datetime import datetime
import sys
import json

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

    # Calcula corretamente o dia do ano de 31 de outubro para o ano referido
    data_outubro = datetime(ano_ref, 10, 31)
    dia_outubro = data_outubro.timetuple().tm_yday

    if dia_inicio > dia_outubro:
        raise ValueError(f"O dia de início ({dia_inicio}) é posterior a 31 de outubro ({dia_outubro}).")

    # Gera intervalo de dias até 31 de outubro (inclusive)
    dias = np.arange(dia_inicio, dia_outubro + 1)

    # Calcula a curva prevista de vazão
    vazao_prevista = vazao_inicial * np.exp(alfa_pond * (dias - dia_inicio))

    # Retorna JSON com os resultados
    resultado = {
        'vazao_prevista': float(vazao_prevista[-1]),
        'vazao_minima': float(vazao_inicial),
        'dia_inicio': int(dia_inicio)
    }

    return json.dumps(resultado)

# Execução via linha de comando
if __name__ == "__main__":
    if len(sys.argv) != 6:
        print(json.dumps({"error": "Uso: python previsao_otimizada.py <arquivo_csv> <mes> <alfa_pond> <q_noventa> <vsup>"}))
        sys.exit(1)

    try:
        nome_arquivo = sys.argv[1]
        mes_interesse = int(sys.argv[2])
        alfa_pond = float(sys.argv[3])
        q_noventa = float(sys.argv[4])
        vsup = float(sys.argv[5])

        resultado = processar_vazao_csv(nome_arquivo, mes_interesse, alfa_pond, q_noventa, vsup)
        print(resultado)
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
