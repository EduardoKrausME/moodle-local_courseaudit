<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brazilian Portuguese language strings for local_courseaudit.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['aiunavailable'] = 'As verificações determinísticas foram concluídas, mas a análise por IA não está disponível: {$a}';
$string['allsections'] = 'Todas as seções';
$string['auditcourse'] = 'Auditar curso';
$string['auditfailed'] = 'Não foi possível concluir a auditoria do curso. Consulte os logs de depuração do Moodle para detalhes técnicos.';
$string['bridgeerror:credits'] = 'O tenant ou usuário não possui créditos suficientes no AI Bridge.';
$string['bridgeerror:failed'] = 'O AI Bridge não conseguiu concluir a requisição.';
$string['bridgeerror:invalidjson'] = 'O provider retornou uma resposta que não é um JSON estruturado válido.';
$string['bridgeerror:nopermission'] = 'O usuário atual não possui permissão para usar o AI Bridge.';
$string['bridgeerror:noroute'] = 'Não há route configurada no AI Bridge para o purpose da auditoria e o papel de IA do usuário.';
$string['bridgeerror:notenant'] = 'Não há tenant habilitado no AI Bridge para este usuário, ou o acesso de IA do usuário está desativado.';
$string['bridgeerror:notinstalled'] = 'O local_ai_bridge não está instalado ou sua classe de API não está disponível.';
$string['bridgeerror:providers'] = 'Todos os providers/routes configurados falharam ou estão indisponíveis.';
$string['bridgeerror:purpose'] = 'O purpose obrigatório “courseaudit-analysis” não existe ou está desativado no AI Bridge.';
$string['cachedresult'] = 'Este resultado foi reutilizado de uma auditoria concluída anteriormente porque o conteúdo relevante do curso não mudou.';
$string['category:accessibility'] = 'Acessibilidade básica';
$string['category:completion'] = 'Conclusão';
$string['category:content'] = 'Conteúdo';
$string['category:dates'] = 'Datas';
$string['category:grading'] = 'Avaliação';
$string['category:links'] = 'Links';
$string['category:pedagogy'] = 'Coerência pedagógica';
$string['category:recommendations'] = 'Recomendações';
$string['category:structure'] = 'Estrutura';
$string['courseaudit:audit'] = 'Auditar a qualidade do curso';
$string['editrelated'] = 'Editar item relacionado';
$string['evidence'] = 'Evidências';
$string['finding:brokenavailability:description'] = 'A condição de disponibilidade referencia ID(s) de módulo que não existem mais: {$a}.';
$string['finding:brokenavailability:title'] = 'Disponibilidade referencia atividade inexistente';
$string['finding:brokenlink:description'] = 'A URL interna do Moodle aponta para uma atividade ou curso que não existe: {$a}.';
$string['finding:brokenlink:title'] = 'Link interno do Moodle quebrado';
$string['finding:completiondisabled:description'] = 'O acompanhamento de conclusão do curso está desativado, portanto não há um caminho de conclusão gerenciado pelo Moodle para auditar.';
$string['finding:completiondisabled:title'] = 'Conclusão de curso desativada';
$string['finding:coursedates:description'] = 'A data final configurada para o curso é anterior à data de início.';
$string['finding:coursedates:title'] = 'Data final do curso anterior à data inicial';
$string['finding:cutoffbeforeduedate:description'] = '“{$a}” possui data limite anterior à data de entrega.';
$string['finding:cutoffbeforeduedate:title'] = 'Data limite anterior à data de entrega';
$string['finding:dateorder:description'] = 'Em “{$a->activity}”, a data de encerramento/entrega ({$a->to}) é anterior à data correspondente de abertura/início ({$a->from}).';
$string['finding:dateorder:title'] = 'Datas da atividade estão fora de ordem';
$string['finding:duplicatecontent:description'] = 'O conteúdo normalizado é idêntico em: {$a}.';
$string['finding:duplicatecontent:title'] = 'Conteúdo idêntico em múltiplas atividades';
$string['finding:emptysection:description'] = 'A seção “{$a}” não contém atividades ou recursos.';
$string['finding:emptysection:title'] = 'Seção vazia';
$string['finding:headingjump:description'] = 'O conteúdo passa de H{$a->from} para H{$a->to}. Revise se a hierarquia representa corretamente a estrutura do documento.';
$string['finding:headingjump:title'] = 'Hierarquia de títulos pula um nível';
$string['finding:hiddenactivity:description'] = '“{$a}” está oculto dos alunos. Isso pode ser intencional, mas vale revisar antes da liberação do curso.';
$string['finding:hiddenactivity:title'] = 'Atividade ou recurso oculto';
$string['finding:iframenotitle:description'] = 'Um iframe incorporado não possui um atributo title significativo.';
$string['finding:iframenotitle:title'] = 'Iframe sem título';
$string['finding:imgnoalt:description'] = 'Uma imagem não possui o atributo alt. Imagens decorativas devem usar alt=""; imagens com significado precisam de uma alternativa textual adequada.';
$string['finding:imgnoalt:title'] = 'Imagem sem atributo alt';
$string['finding:invalidavailability:activity'] = 'O JSON de disponibilidade da atividade “{$a}” é inválido.';
$string['finding:invalidavailability:section'] = 'O JSON de disponibilidade da seção “{$a}” é inválido.';
$string['finding:invalidavailability:title'] = 'Configuração de disponibilidade inválida';
$string['finding:invalidurl:description'] = 'O conteúdo possui uma URL que não é HTTP/HTTPS válida nem uma URL relativa local suportada: {$a}.';
$string['finding:invalidurl:title'] = 'URL aparentemente inválida';
$string['finding:missinggradeitem:description'] = '“{$a}” possui nota ou escala configurada, mas não foi encontrado o item de nota correspondente no Moodle.';
$string['finding:missinggradeitem:title'] = 'Atividade avaliativa sem item de nota';
$string['finding:nocompletion:description'] = '“{$a}” está visível, mas não utiliza acompanhamento de conclusão da atividade.';
$string['finding:nocompletion:title'] = 'Atividade sem regra de conclusão';
$string['finding:nocoursecriteria:description'] = 'O acompanhamento de conclusão está habilitado, mas nenhum critério de conclusão do curso foi configurado.';
$string['finding:nocoursecriteria:title'] = 'Curso sem critérios de conclusão';
$string['finding:nodescription:description'] = '“{$a}” não possui introdução/descrição. Para este tipo de atividade, revise se o aluno recebe instruções e contexto suficientes.';
$string['finding:nodescription:title'] = 'Atividade sem descrição';
$string['finding:vaguelink:description'] = 'O texto de link “{$a}” não descreve claramente o destino quando lido fora do contexto ao redor.';
$string['finding:vaguelink:title'] = 'Texto do link é vago';
$string['forcerun'] = 'Reexecutar mesmo quando o conteúdo do curso não mudou';
$string['forcerun_help'] = 'Por padrão, uma auditoria concluída é reutilizada quando curso, modo e seção selecionada produzem o mesmo hash de conteúdo. Ative esta opção para forçar todas as verificações e, quando aplicável, uma nova chamada de IA.';
$string['intro'] = 'Primeiro verifica configurações objetivas do Moodle e usa IA apenas para interpretação semântica e pedagógica. Nenhuma correção é aplicada automaticamente.';
$string['invalidmode'] = 'Modo de auditoria inválido.';
$string['invalidsection'] = 'A seção selecionada não pertence a este curso.';
$string['mode'] = 'Modo de auditoria';
$string['modecontentai'] = 'Somente conteúdo e análise por IA';
$string['modefull'] = 'Auditoria completa';
$string['modestructure'] = 'Somente estrutura e regras determinísticas';
$string['nofindings'] = 'Nenhum apontamento foi produzido para o escopo selecionado.';
$string['origin'] = 'Origem';
$string['origin:ai'] = 'Análise por IA';
$string['origin:rule'] = 'Regra Moodle';
$string['pluginname'] = 'Auditoria de curso';
$string['privacy:metadata:runs'] = 'Execuções de auditoria armazenadas para evitar repetir trabalho determinístico e chamadas de IA idênticas.';
$string['privacy:metadata:runs:aiused'] = 'Se a auditoria incluiu uma análise por IA concluída com sucesso.';
$string['privacy:metadata:runs:contenthash'] = 'Um hash unidirecional do snapshot normalizado relevante do curso usado para detectar auditorias sem alterações.';
$string['privacy:metadata:runs:courseid'] = 'O curso auditado.';
$string['privacy:metadata:runs:findings'] = 'Somente findings estruturados; prompts brutos e respostas brutas do modelo não são armazenados.';
$string['privacy:metadata:runs:mode'] = 'O modo de auditoria selecionado.';
$string['privacy:metadata:runs:sectionid'] = 'O escopo de seção selecionado, ou zero para o curso inteiro.';
$string['privacy:metadata:runs:status'] = 'Se a auditoria foi concluída integralmente ou apenas parcialmente.';
$string['privacy:metadata:runs:timecreated'] = 'Quando a auditoria foi executada.';
$string['privacy:metadata:runs:userid'] = 'O usuário que iniciou a auditoria.';
$string['privacy:path'] = 'Execuções da auditoria de curso';
$string['related'] = 'Item relacionado';
$string['runaudit'] = 'Executar auditoria';
$string['sectionfilter'] = 'Seção';
$string['sectionzero'] = 'Seção geral';
$string['severity:ai_insight'] = 'INSIGHT DE IA';
$string['severity:error'] = 'ERRO';
$string['severity:suggestion'] = 'SUGESTÃO';
$string['severity:warning'] = 'ALERTA';
$string['suggestion'] = 'Sugestão';
$string['summaryai'] = 'insights de IA';
$string['summaryerrors'] = 'erros';
$string['summarysuggestions'] = 'sugestões';
$string['summarywarnings'] = 'alertas';

$string['analysis_ai_block'] = 'Análise de IA do Course Audit';
$string['analysis_close'] = 'Fechar';
$string['analysis_error'] = 'Não foi possível analisar esta atividade.';
$string['analysis_excluded_plugins'] = 'Módulos excluídos da análise de atividades';
$string['analysis_excluded_plugins_desc'] = 'Os módulos selecionados não exibirão os controles de análise e serão excluídos da análise atividade por atividade.';
$string['analysis_last'] = 'Última análise';
$string['analysis_latest'] = 'Análise mais recente';
$string['analysis_model_warning'] = 'Esta análise usou um modelo mini/nano. Para uma análise mais profunda, configure a rota courseaudit-analysis no AI Bridge com um modelo maior.';
$string['analysis_no_content'] = 'Nenhum conteúdo de análise foi retornado.';
$string['analysis_not_supported'] = 'Este tipo de atividade não está disponível para análise no Course Audit.';
$string['analysis_print'] = 'Imprimir';
$string['analysis_print_analysis'] = 'Imprimir análise';
$string['analysis_print_popup_blocked'] = 'O navegador bloqueou a aba de impressão. Permita pop-ups e tente novamente.';
$string['analysis_reanalyze'] = 'Analisar novamente';
$string['analysis_recommendations'] = 'Recomendações';
$string['analysis_result'] = 'Análise da atividade';
$string['analysis_status_insufficient'] = 'Insuficiente';
$string['analysis_status_needs_review'] = 'Precisa de revisão';
$string['analysis_status_ok'] = 'OK';
$string['analysis_status_ok_minor'] = 'OK com pequenos ajustes';
$string['analyze_activity'] = 'Analisar com IA';
$string['analyze_course'] = 'Analisar atividades do curso com IA';
$string['analyzing_activity'] = 'Analisando ortografia, coerência pedagógica e taxonomia de Bloom...';
$string['analyzing_course'] = 'Analisando as atividades do curso...';
$string['prompt_activity_focus_alignment'] = 'priorize a coerência entre curso, seção, título e conteúdo da atividade.';
$string['prompt_activity_focus_bloom'] = 'priorize a taxonomia de Bloom e a profundidade cognitiva da proposta.';
$string['prompt_activity_focus_full'] = 'análise completa da atividade.';
$string['prompt_activity_focus_pedagogy'] = 'priorize a adequação pedagógica, as instruções ao estudante e a qualidade da aprendizagem.';
$string['prompt_activity_focus_spelling'] = 'priorize ortografia, gramática, clareza e tom instrucional.';
$string['prompt_activity_schema_bloom_level'] = 'remember | understand | apply | analyze | evaluate | create';
$string['prompt_activity_schema_diagnosis'] = 'Resumo curto do diagnóstico geral.';
$string['prompt_activity_schema_recommendation_1'] = 'Ação prática 1.';
$string['prompt_activity_schema_recommendation_2'] = 'Ação prática 2.';
$string['prompt_activity_schema_status'] = 'OK | OK with minor adjustments | Needs review | Inadequate or insufficient';
$string['prompt_activity_schema_status_key'] = 'ok | ok_minor | needs_review | insufficient';
$string['prompt_activity_system'] = 'Você é especialista em design instrucional, revisão de texto e Moodle.

Analise uma atividade Moodle existente usando somente o conteúdo autoral fornecido. Nunca infira dados de alunos.
Escreva a análise Markdown visível no idioma atual do Moodle: {$a->lang}.
Mantenha nomes dos campos técnicos do JSON e valores enum em inglês.
Se o conteúdo for insuficiente, diga isso claramente.

Critérios:
1. Ortografia, gramática e clareza textual.
2. Coerência entre título da atividade, seção e conteúdo.
3. Nível predominante de Bloom: remember, understand, apply, analyze, evaluate, create.
4. Adequação pedagógica.
5. Sugestões práticas de melhoria.

Foco adicional: {$a->focus}

Retorne Markdown visível com diagnóstico, ortografia/clareza, coerência com a seção, Bloom, melhorias e opinião final.
A classificação final deve ser exatamente uma destas: OK, OK with minor adjustments, Needs review, Inadequate or insufficient.
Tipo de análise solicitado: {$a->analysis}';
$string['prompt_activity_user'] = 'Analise a atividade do Moodle abaixo.

{$a}';
$string['privacy:metadata:analysis'] = 'Análises pedagógicas por atividade armazenadas no histórico do Course Audit.';
$string['privacy:metadata:analysis:courseid'] = 'O curso que contém a atividade analisada.';
$string['privacy:metadata:analysis:cmid'] = 'O módulo de curso analisado.';
$string['privacy:metadata:analysis:userid'] = 'O usuário que solicitou a análise.';
$string['privacy:metadata:analysis:contenthash'] = 'Hash usado para reutilizar a análise quando o conteúdo autoral da atividade não mudou.';
$string['privacy:metadata:analysis:result'] = 'Status estruturado, nível de Bloom, recomendações e texto da análise.';
$string['privacy:metadata:analysis:timecreated'] = 'Quando a análise da atividade foi criada.';
$string['privacy:analysispath'] = 'Análises de atividades';
