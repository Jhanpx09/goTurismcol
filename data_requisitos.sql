INSERT INTO `requisito_viaje`
(`id_requisito`, `id_destino`, `titulo_requisito`, `descripcion_requisito`, `tipo_requisito`, `fuente_oficial`, `fecha_ultima_actualizacion`, `creado_por`, `estado`, `icono`)
VALUES

-- =========================
-- ANDORRA (id_destino = 3)
-- =========================
(NULL, 3, 'Pasaporte vigente', 'Debes viajar con pasaporte colombiano vigente y en buen estado. Aunque Andorra no pertenece al espacio Schengen, el ingreso normalmente se hace por España o Francia, por lo que tu pasaporte será revisado en esos controles migratorios. Se recomienda tener vigencia suficiente para todo el viaje y posibles imprevistos.', 'obligatorio', 'https://visitandorra.com/en/visitor-information/passports-visas-and-customs/', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 3, 'Ingreso por España o Francia (Schengen)', 'Andorra no tiene aeropuerto, por lo que el acceso es por carretera desde España o Francia. Esto significa que el control migratorio principal ocurre al entrar a Schengen, y allí pueden solicitarte documentos de soporte del viaje. En Andorra es común encontrar controles aduaneros, pero no siempre sellan pasaporte.', 'obligatorio', 'https://visitandorra.com/en/visitor-information/passports-visas-and-customs/', '2026-01-21', 1, 'vigente', 'warning'),

(NULL, 3, 'Visa Schengen (si aplica)', 'Como colombiano normalmente no necesitas visa Schengen para turismo de corta estancia (hasta 90 días), pero esto depende de que cumplas los requisitos de entrada. Si por tu situación particular necesitas visa para entrar a España o Francia, entonces también la necesitarás para poder llegar a Andorra. Si planeas salir de Schengen y volver a entrar, revisa si necesitas múltiples entradas.', 'obligatorio', 'https://www.eeas.europa.eu/colombia/travel-study_en?s=160', '2026-01-21', 1, 'vigente', 'warning'),

(NULL, 3, 'Soporte de viaje (alojamiento)', 'Es recomendable llevar una reserva de hotel, Airbnb o dirección del lugar donde te hospedarás. Este documento puede ser solicitado por migración al ingresar por España o Francia para confirmar que tu viaje es turístico. También ayuda a evitar demoras o preguntas adicionales en el control.', 'recomendado', 'https://www.aena.es/en/passengers/documentation-visas/immigration-visas/foreigner-travelling-spain.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 3, 'Tiquete de salida o regreso', 'Lleva un tiquete de regreso a Colombia o de salida del espacio Schengen dentro del tiempo permitido. Este requisito es común en controles migratorios y también puede ser exigido por la aerolínea antes de abordar. Sirve para demostrar que tu visita es temporal y que no excederás el tiempo autorizado.', 'recomendado', 'https://www.aena.es/en/passengers/documentation-visas/immigration-visas/foreigner-travelling-spain.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 3, 'Seguro médico de viaje', 'Aunque no siempre es obligatorio, es muy recomendable viajar con seguro médico internacional. En caso de emergencia, atención médica o accidente, este seguro puede cubrir gastos altos que normalmente no cubre ningún sistema público para turistas. Además, puede ser solicitado como parte de los documentos de soporte al ingresar por Schengen.', 'recomendado', 'https://www.eeas.europa.eu/colombia/travel-study_en?s=160', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),


-- ==========================================
-- EMIRATOS ÁRABES UNIDOS (id_destino = 4)
-- ==========================================
(NULL, 4, 'Exención de visa (turismo)', 'Según la misión oficial de Emiratos Árabes Unidos en Bogotá, los colombianos pueden ingresar por turismo sin tramitar visa previa. Esta condición aplica para estancias cortas y está sujeta a verificación en el control migratorio. Aun sin visa, debes cumplir con los demás requisitos de entrada.', 'obligatorio', 'https://www.mofa.gov.ae/en/Missions/Bogota/Services/Visas', '2026-01-21', 1, 'vigente', 'warning'),

(NULL, 4, 'Pasaporte vigente', 'Debes viajar con pasaporte colombiano vigente y en buen estado. Es común que las aerolíneas y migración verifiquen que el pasaporte tenga vigencia suficiente para tu estancia. Para evitar inconvenientes, se recomienda contar con varios meses de vigencia antes del viaje.', 'obligatorio', 'https://www.mofa.gov.ae/en/Missions/Bogota/Services/Visas', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 4, 'Tiquete de salida', 'Lleva tiquete de salida de Emiratos (regreso a Colombia o continuación a otro país). Este documento puede ser solicitado por migración para confirmar que tu viaje es temporal. También es un requisito frecuente de las aerolíneas al momento del check-in.', 'obligatorio', 'https://www.mofa.gov.ae/en/Missions/Bogota/Services/Visas', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 4, 'Reserva de alojamiento', 'Es recomendable contar con reserva de hotel o dirección del lugar donde te hospedarás. Migración puede solicitar esta información para validar tu estadía turística y duración del viaje. Tener el comprobante a mano reduce preguntas y posibles demoras.', 'recomendado', 'https://www.mofa.gov.ae/en/Missions/Bogota/Services/Visas', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 4, 'Fondos para la estadía', 'Debes contar con dinero suficiente para cubrir hospedaje, alimentación, transporte y gastos durante el viaje. Aunque no siempre lo piden, es posible que te soliciten demostrar solvencia en el control migratorio. Se recomienda llevar tarjetas y/o soportes bancarios.', 'recomendado', 'https://www.mofa.gov.ae/en/Missions/Bogota/Services/Visas', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 4, 'Seguro médico de viaje', 'Se recomienda viajar con seguro médico internacional, especialmente por los costos elevados de atención médica en el exterior. Esto te protege ante emergencias, accidentes o enfermedades inesperadas. Además, puede ser útil si te lo solicitan como soporte del viaje.', 'recomendado', 'https://www.mofa.gov.ae/en/Missions/Bogota/Services/Visas', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),


-- ===========================
-- AFGANISTÁN (id_destino = 5)
-- ===========================
(NULL, 5, 'Visa obligatoria', 'Para ingresar a Afganistán con fines turísticos normalmente se requiere visa tramitada antes del viaje. La visa debe obtenerse mediante los canales oficiales disponibles, y sin ella es muy probable que no puedas abordar o ingresar. Confirma siempre el procedimiento vigente antes de comprar tiquetes.', 'obligatorio', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Afghanistan.html', '2026-01-21', 1, 'vigente', 'warning'),

(NULL, 5, 'Pasaporte vigente', 'Debes contar con pasaporte colombiano vigente y en buen estado. Las autoridades suelen exigir vigencia mínima para permitir el ingreso y para procesar la visa. Se recomienda que tenga suficiente vigencia para todo el viaje y posibles cambios de itinerario.', 'obligatorio', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Afghanistan.html', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 5, 'Páginas en blanco', 'Asegúrate de tener páginas en blanco disponibles en el pasaporte. Esto es necesario para sellos migratorios y, en muchos casos, para el visado o documentos adicionales. Si tu pasaporte está lleno, podrías tener problemas al viajar.', 'obligatorio', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Afghanistan.html', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 5, 'Tiquete de salida', 'Es recomendable contar con un plan claro de salida del país y tiquete de retorno o continuación. En algunos controles migratorios esto se solicita para confirmar que tu visita es temporal. También puede ser exigido por aerolíneas o autoridades de tránsito.', 'recomendado', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Afghanistan.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 5, 'Recomendaciones sanitarias', 'Antes de viajar, revisa vacunas recomendadas y condiciones sanitarias del destino. Dependiendo del itinerario y zonas visitadas, podrías requerir vacunas o medidas preventivas adicionales. Esto reduce riesgos de salud durante el viaje.', 'recomendado', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Afghanistan.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 5, 'Alertas y seguridad', 'Es fundamental revisar alertas oficiales de seguridad antes de viajar. Afganistán puede presentar condiciones de riesgo que afecten rutas, transporte y estadía. Planificar con información oficial ayuda a evitar situaciones peligrosas.', 'recomendado', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Afghanistan.html', '2026-01-21', 1, 'vigente', 'warning'),


-- ==================================
-- ANTIGUA Y BARBUDA (id_destino = 6)
-- ==================================
(NULL, 6, 'Exención de visa', 'Antigua y Barbuda publica una lista oficial de países exentos de visa, donde se incluye Colombia. Esto significa que puedes viajar como turista sin tramitar visa previa, siempre que cumplas los demás requisitos de entrada. La duración exacta de la estadía la define migración al llegar.', 'obligatorio', 'https://immigration.gov.ag/visa-services/countries-exempt-from-visa/', '2026-01-21', 1, 'vigente', 'warning'),

(NULL, 6, 'Formulario ArriveAntigua', 'Antes del viaje debes completar el formulario de llegada ArriveAntigua. Este registro facilita el proceso migratorio y puede ser requerido para abordar o al ingresar. Se recomienda hacerlo con anticipación y guardar el comprobante.', 'obligatorio', 'https://www.visitantiguabarbuda.com/visa-requirements/', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 6, 'Pasaporte vigente', 'Debes ingresar con pasaporte colombiano vigente y en buen estado. Es recomendable que tenga vigencia suficiente para toda la estadía y para cumplir con requisitos de aerolíneas. Un pasaporte deteriorado puede causar rechazo en migración.', 'obligatorio', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/AntiguaandBarbuda.html', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 6, 'Tiquete de salida', 'Lleva un tiquete de regreso o de continuación del viaje. Este requisito es común para visitantes, ya que demuestra que no planeas quedarte de forma irregular. También puede ser exigido por la aerolínea antes de permitir el embarque.', 'obligatorio', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/AntiguaandBarbuda.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 6, 'Reserva de alojamiento', 'Es recomendable tener reserva de hotel o dirección del lugar donde te hospedarás. Migración puede solicitarlo para validar tu plan turístico y el tiempo de permanencia. Tener esta información organizada agiliza el ingreso.', 'recomendado', 'https://www.visitantiguabarbuda.com/visa-requirements/', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 6, 'Seguro médico de viaje', 'Se recomienda viajar con seguro médico internacional para emergencias. En caso de accidente o enfermedad, los costos pueden ser altos para turistas. Contar con seguro reduce riesgos financieros durante el viaje.', 'recomendado', 'https://www.visitantiguabarbuda.com/visa-requirements/', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),


-- =========================
-- ANGUILLA (id_destino = 7)
-- =========================
(NULL, 7, 'Pasaporte vigente', 'Debes ingresar con pasaporte colombiano vigente y en buen estado. Es importante que tenga vigencia suficiente para cubrir toda tu estadía y el retorno. La aerolínea también puede validar este requisito antes de abordar.', 'obligatorio', 'https://ivisitanguilla.com/entry-requirements/', '2026-01-21', 1, 'vigente', 'assignment_ind'),

(NULL, 7, 'Tiquete de regreso o continuación', 'Debes contar con tiquete de salida del territorio (regreso a Colombia o viaje a otro país). Este requisito se usa para confirmar que tu visita es temporal y con fines turísticos. Es común que sea solicitado tanto por aerolínea como por migración.', 'obligatorio', 'https://ivisitanguilla.com/entry-requirements/', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 7, 'Visa (según caso)', 'Para turismo, normalmente no se exige visa a visitantes colombianos, pero esto puede depender de tu ruta de viaje y escalas. Si haces tránsito por países que sí exigen visa, podrías necesitar permisos adicionales. Se recomienda confirmar requisitos de tránsito antes de comprar tiquetes.', 'obligatorio', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Anguilla.html', '2026-01-21', 1, 'vigente', 'warning'),

(NULL, 7, 'Fondos para la estadía', 'Es recomendable demostrar que cuentas con dinero suficiente para tu hospedaje, alimentación y transporte. Aunque no siempre lo solicitan, puede ser requerido en control migratorio. Lleva tarjetas y soportes bancarios si es necesario.', 'recomendado', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Anguilla.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 7, 'Reserva de alojamiento', 'Se recomienda tener reserva de hotel o información clara del hospedaje. Esto ayuda a justificar el motivo turístico y el tiempo de permanencia. También evita preguntas adicionales durante el ingreso.', 'recomendado', 'https://ivisitanguilla.com/entry-requirements/', '2026-01-21', 1, 'vigente', 'fiber_manual_record'),

(NULL, 7, 'Seguro médico de viaje', 'Es recomendable viajar con seguro médico internacional para emergencias. Como turista, cualquier atención médica puede generar costos altos. Un seguro te protege ante accidentes o enfermedades inesperadas.', 'recomendado', 'https://travel.state.gov/content/travel/en/international-travel/International-Travel-Country-Information-Pages/Anguilla.html', '2026-01-21', 1, 'vigente', 'fiber_manual_record');
