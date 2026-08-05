<?php

declare(strict_types=1);

final class LeadPhoneCountries
{
    /**
     * @return list<array{iso:string,dial:string,name_pt:string,name_en:string,mask:string}>
     */
    public static function all(): array
    {
        return [
            ['iso' => 'BR', 'dial' => '55', 'name_pt' => 'Brasil', 'name_en' => 'Brazil', 'mask' => '(00) 00000-0000'],
            ['iso' => 'US', 'dial' => '1', 'name_pt' => 'Estados Unidos', 'name_en' => 'United States', 'mask' => '(000) 000-0000'],
            ['iso' => 'PT', 'dial' => '351', 'name_pt' => 'Portugal', 'name_en' => 'Portugal', 'mask' => '000 000 000'],
            ['iso' => 'AR', 'dial' => '54', 'name_pt' => 'Argentina', 'name_en' => 'Argentina', 'mask' => '00 0000-0000'],
            ['iso' => 'BO', 'dial' => '591', 'name_pt' => 'Bolívia', 'name_en' => 'Bolivia', 'mask' => '0 000 0000'],
            ['iso' => 'CL', 'dial' => '56', 'name_pt' => 'Chile', 'name_en' => 'Chile', 'mask' => '0 0000 0000'],
            ['iso' => 'CO', 'dial' => '57', 'name_pt' => 'Colômbia', 'name_en' => 'Colombia', 'mask' => '000 0000000'],
            ['iso' => 'CR', 'dial' => '506', 'name_pt' => 'Costa Rica', 'name_en' => 'Costa Rica', 'mask' => '0000 0000'],
            ['iso' => 'CU', 'dial' => '53', 'name_pt' => 'Cuba', 'name_en' => 'Cuba', 'mask' => '0 0000000'],
            ['iso' => 'DO', 'dial' => '1', 'name_pt' => 'República Dominicana', 'name_en' => 'Dominican Republic', 'mask' => '(000) 000-0000'],
            ['iso' => 'EC', 'dial' => '593', 'name_pt' => 'Equador', 'name_en' => 'Ecuador', 'mask' => '00 000 0000'],
            ['iso' => 'SV', 'dial' => '503', 'name_pt' => 'El Salvador', 'name_en' => 'El Salvador', 'mask' => '0000 0000'],
            ['iso' => 'GT', 'dial' => '502', 'name_pt' => 'Guatemala', 'name_en' => 'Guatemala', 'mask' => '0000 0000'],
            ['iso' => 'HN', 'dial' => '504', 'name_pt' => 'Honduras', 'name_en' => 'Honduras', 'mask' => '0000 0000'],
            ['iso' => 'MX', 'dial' => '52', 'name_pt' => 'México', 'name_en' => 'Mexico', 'mask' => '00 0000 0000'],
            ['iso' => 'NI', 'dial' => '505', 'name_pt' => 'Nicarágua', 'name_en' => 'Nicaragua', 'mask' => '0000 0000'],
            ['iso' => 'PA', 'dial' => '507', 'name_pt' => 'Panamá', 'name_en' => 'Panama', 'mask' => '0000 0000'],
            ['iso' => 'PY', 'dial' => '595', 'name_pt' => 'Paraguai', 'name_en' => 'Paraguay', 'mask' => '000 000000'],
            ['iso' => 'PE', 'dial' => '51', 'name_pt' => 'Peru', 'name_en' => 'Peru', 'mask' => '000 000 000'],
            ['iso' => 'UY', 'dial' => '598', 'name_pt' => 'Uruguai', 'name_en' => 'Uruguay', 'mask' => '0 000 00 00'],
            ['iso' => 'VE', 'dial' => '58', 'name_pt' => 'Venezuela', 'name_en' => 'Venezuela', 'mask' => '000 0000000'],
            ['iso' => 'CA', 'dial' => '1', 'name_pt' => 'Canadá', 'name_en' => 'Canada', 'mask' => '(000) 000-0000'],
            ['iso' => 'GB', 'dial' => '44', 'name_pt' => 'Reino Unido', 'name_en' => 'United Kingdom', 'mask' => '0000 000000'],
            ['iso' => 'IE', 'dial' => '353', 'name_pt' => 'Irlanda', 'name_en' => 'Ireland', 'mask' => '00 000 0000'],
            ['iso' => 'ES', 'dial' => '34', 'name_pt' => 'Espanha', 'name_en' => 'Spain', 'mask' => '000 00 00 00'],
            ['iso' => 'FR', 'dial' => '33', 'name_pt' => 'França', 'name_en' => 'France', 'mask' => '0 00 00 00 00'],
            ['iso' => 'DE', 'dial' => '49', 'name_pt' => 'Alemanha', 'name_en' => 'Germany', 'mask' => '000 00000000'],
            ['iso' => 'IT', 'dial' => '39', 'name_pt' => 'Itália', 'name_en' => 'Italy', 'mask' => '000 000 0000'],
            ['iso' => 'CH', 'dial' => '41', 'name_pt' => 'Suíça', 'name_en' => 'Switzerland', 'mask' => '00 000 00 00'],
            ['iso' => 'AT', 'dial' => '43', 'name_pt' => 'Áustria', 'name_en' => 'Austria', 'mask' => '000 0000000'],
            ['iso' => 'BE', 'dial' => '32', 'name_pt' => 'Bélgica', 'name_en' => 'Belgium', 'mask' => '000 00 00 00'],
            ['iso' => 'NL', 'dial' => '31', 'name_pt' => 'Países Baixos', 'name_en' => 'Netherlands', 'mask' => '0 00000000'],
            ['iso' => 'PL', 'dial' => '48', 'name_pt' => 'Polônia', 'name_en' => 'Poland', 'mask' => '000 000 000'],
            ['iso' => 'SE', 'dial' => '46', 'name_pt' => 'Suécia', 'name_en' => 'Sweden', 'mask' => '00 000 00 00'],
            ['iso' => 'NO', 'dial' => '47', 'name_pt' => 'Noruega', 'name_en' => 'Norway', 'mask' => '000 00 000'],
            ['iso' => 'DK', 'dial' => '45', 'name_pt' => 'Dinamarca', 'name_en' => 'Denmark', 'mask' => '00 00 00 00'],
            ['iso' => 'FI', 'dial' => '358', 'name_pt' => 'Finlândia', 'name_en' => 'Finland', 'mask' => '00 0000000'],
            ['iso' => 'GR', 'dial' => '30', 'name_pt' => 'Grécia', 'name_en' => 'Greece', 'mask' => '000 000 0000'],
            ['iso' => 'TR', 'dial' => '90', 'name_pt' => 'Turquia', 'name_en' => 'Turkey', 'mask' => '000 000 00 00'],
            ['iso' => 'RU', 'dial' => '7', 'name_pt' => 'Rússia', 'name_en' => 'Russia', 'mask' => '000 000-00-00'],
            ['iso' => 'UA', 'dial' => '380', 'name_pt' => 'Ucrânia', 'name_en' => 'Ukraine', 'mask' => '00 000 0000'],
            ['iso' => 'IL', 'dial' => '972', 'name_pt' => 'Israel', 'name_en' => 'Israel', 'mask' => '00 000 0000'],
            ['iso' => 'AE', 'dial' => '971', 'name_pt' => 'Emirados Árabes', 'name_en' => 'United Arab Emirates', 'mask' => '00 000 0000'],
            ['iso' => 'SA', 'dial' => '966', 'name_pt' => 'Arábia Saudita', 'name_en' => 'Saudi Arabia', 'mask' => '00 000 0000'],
            ['iso' => 'ZA', 'dial' => '27', 'name_pt' => 'África do Sul', 'name_en' => 'South Africa', 'mask' => '00 000 0000'],
            ['iso' => 'EG', 'dial' => '20', 'name_pt' => 'Egito', 'name_en' => 'Egypt', 'mask' => '000 000 0000'],
            ['iso' => 'NG', 'dial' => '234', 'name_pt' => 'Nigéria', 'name_en' => 'Nigeria', 'mask' => '000 000 0000'],
            ['iso' => 'IN', 'dial' => '91', 'name_pt' => 'Índia', 'name_en' => 'India', 'mask' => '00000 00000'],
            ['iso' => 'CN', 'dial' => '86', 'name_pt' => 'China', 'name_en' => 'China', 'mask' => '000 0000 0000'],
            ['iso' => 'JP', 'dial' => '81', 'name_pt' => 'Japão', 'name_en' => 'Japan', 'mask' => '00 0000 0000'],
            ['iso' => 'KR', 'dial' => '82', 'name_pt' => 'Coreia do Sul', 'name_en' => 'South Korea', 'mask' => '00 0000 0000'],
            ['iso' => 'AU', 'dial' => '61', 'name_pt' => 'Austrália', 'name_en' => 'Australia', 'mask' => '000 000 000'],
            ['iso' => 'NZ', 'dial' => '64', 'name_pt' => 'Nova Zelândia', 'name_en' => 'New Zealand', 'mask' => '00 000 0000'],
            ['iso' => 'PH', 'dial' => '63', 'name_pt' => 'Filipinas', 'name_en' => 'Philippines', 'mask' => '000 000 0000'],
            ['iso' => 'SG', 'dial' => '65', 'name_pt' => 'Singapura', 'name_en' => 'Singapore', 'mask' => '0000 0000'],
            ['iso' => 'TH', 'dial' => '66', 'name_pt' => 'Tailândia', 'name_en' => 'Thailand', 'mask' => '00 000 0000'],
            ['iso' => 'VN', 'dial' => '84', 'name_pt' => 'Vietnã', 'name_en' => 'Vietnam', 'mask' => '00 0000 000'],
            ['iso' => 'ID', 'dial' => '62', 'name_pt' => 'Indonésia', 'name_en' => 'Indonesia', 'mask' => '000 0000 0000'],
            ['iso' => 'MY', 'dial' => '60', 'name_pt' => 'Malásia', 'name_en' => 'Malaysia', 'mask' => '00 0000 0000'],
            ['iso' => 'AF', 'dial' => '93', 'name_pt' => 'Afeganistão', 'name_en' => 'Afghanistan', 'mask' => '00 000 0000'],
            ['iso' => 'AL', 'dial' => '355', 'name_pt' => 'Albânia', 'name_en' => 'Albania', 'mask' => '00 000 0000'],
            ['iso' => 'DZ', 'dial' => '213', 'name_pt' => 'Argélia', 'name_en' => 'Algeria', 'mask' => '000 00 00 00'],
            ['iso' => 'AD', 'dial' => '376', 'name_pt' => 'Andorra', 'name_en' => 'Andorra', 'mask' => '000 000'],
            ['iso' => 'AO', 'dial' => '244', 'name_pt' => 'Angola', 'name_en' => 'Angola', 'mask' => '000 000 000'],
            ['iso' => 'AI', 'dial' => '1', 'name_pt' => 'Anguilla', 'name_en' => 'Anguilla', 'mask' => '(000) 000-0000'],
            ['iso' => 'AG', 'dial' => '1', 'name_pt' => 'Antígua e Barbuda', 'name_en' => 'Antigua and Barbuda', 'mask' => '(000) 000-0000'],
            ['iso' => 'AM', 'dial' => '374', 'name_pt' => 'Armênia', 'name_en' => 'Armenia', 'mask' => '00 000000'],
            ['iso' => 'AW', 'dial' => '297', 'name_pt' => 'Aruba', 'name_en' => 'Aruba', 'mask' => '000 0000'],
            ['iso' => 'AZ', 'dial' => '994', 'name_pt' => 'Azerbaijão', 'name_en' => 'Azerbaijan', 'mask' => '00 000 00 00'],
            ['iso' => 'BS', 'dial' => '1', 'name_pt' => 'Bahamas', 'name_en' => 'Bahamas', 'mask' => '(000) 000-0000'],
            ['iso' => 'BH', 'dial' => '973', 'name_pt' => 'Bahrein', 'name_en' => 'Bahrain', 'mask' => '0000 0000'],
            ['iso' => 'BD', 'dial' => '880', 'name_pt' => 'Bangladesh', 'name_en' => 'Bangladesh', 'mask' => '0000 000000'],
            ['iso' => 'BB', 'dial' => '1', 'name_pt' => 'Barbados', 'name_en' => 'Barbados', 'mask' => '(000) 000-0000'],
            ['iso' => 'BY', 'dial' => '375', 'name_pt' => 'Belarus', 'name_en' => 'Belarus', 'mask' => '00 000-00-00'],
            ['iso' => 'BZ', 'dial' => '501', 'name_pt' => 'Belize', 'name_en' => 'Belize', 'mask' => '000 0000'],
            ['iso' => 'BJ', 'dial' => '229', 'name_pt' => 'Benim', 'name_en' => 'Benin', 'mask' => '00 00 00 00'],
            ['iso' => 'BM', 'dial' => '1', 'name_pt' => 'Bermudas', 'name_en' => 'Bermuda', 'mask' => '(000) 000-0000'],
            ['iso' => 'BA', 'dial' => '387', 'name_pt' => 'Bósnia e Herzegovina', 'name_en' => 'Bosnia and Herzegovina', 'mask' => '00 000 000'],
            ['iso' => 'BW', 'dial' => '267', 'name_pt' => 'Botsuana', 'name_en' => 'Botswana', 'mask' => '00 000 000'],
            ['iso' => 'BN', 'dial' => '673', 'name_pt' => 'Brunei', 'name_en' => 'Brunei', 'mask' => '000 0000'],
            ['iso' => 'BG', 'dial' => '359', 'name_pt' => 'Bulgária', 'name_en' => 'Bulgaria', 'mask' => '000 000 000'],
            ['iso' => 'CV', 'dial' => '238', 'name_pt' => 'Cabo Verde', 'name_en' => 'Cape Verde', 'mask' => '000 00 00'],
            ['iso' => 'KH', 'dial' => '855', 'name_pt' => 'Camboja', 'name_en' => 'Cambodia', 'mask' => '00 000 000'],
            ['iso' => 'CM', 'dial' => '237', 'name_pt' => 'Camarões', 'name_en' => 'Cameroon', 'mask' => '0 00 00 00 00'],
            ['iso' => 'KY', 'dial' => '1', 'name_pt' => 'Ilhas Cayman', 'name_en' => 'Cayman Islands', 'mask' => '(000) 000-0000'],
            ['iso' => 'CZ', 'dial' => '420', 'name_pt' => 'Chéquia', 'name_en' => 'Czechia', 'mask' => '000 000 000'],
            ['iso' => 'CI', 'dial' => '225', 'name_pt' => 'Costa do Marfim', 'name_en' => 'Ivory Coast', 'mask' => '00 00 00 00 00'],
            ['iso' => 'HR', 'dial' => '385', 'name_pt' => 'Croácia', 'name_en' => 'Croatia', 'mask' => '00 000 0000'],
            ['iso' => 'CY', 'dial' => '357', 'name_pt' => 'Chipre', 'name_en' => 'Cyprus', 'mask' => '00 000000'],
            ['iso' => 'EE', 'dial' => '372', 'name_pt' => 'Estónia', 'name_en' => 'Estonia', 'mask' => '0000 0000'],
            ['iso' => 'ET', 'dial' => '251', 'name_pt' => 'Etiópia', 'name_en' => 'Ethiopia', 'mask' => '00 000 0000'],
            ['iso' => 'FJ', 'dial' => '679', 'name_pt' => 'Fiji', 'name_en' => 'Fiji', 'mask' => '000 0000'],
            ['iso' => 'GE', 'dial' => '995', 'name_pt' => 'Geórgia', 'name_en' => 'Georgia', 'mask' => '000 00 00 00'],
            ['iso' => 'GH', 'dial' => '233', 'name_pt' => 'Gana', 'name_en' => 'Ghana', 'mask' => '00 000 0000'],
            ['iso' => 'GI', 'dial' => '350', 'name_pt' => 'Gibraltar', 'name_en' => 'Gibraltar', 'mask' => '00000000'],
            ['iso' => 'HT', 'dial' => '509', 'name_pt' => 'Haiti', 'name_en' => 'Haiti', 'mask' => '0000 0000'],
            ['iso' => 'HK', 'dial' => '852', 'name_pt' => 'Hong Kong', 'name_en' => 'Hong Kong', 'mask' => '0000 0000'],
            ['iso' => 'HU', 'dial' => '36', 'name_pt' => 'Hungria', 'name_en' => 'Hungary', 'mask' => '00 000 0000'],
            ['iso' => 'IS', 'dial' => '354', 'name_pt' => 'Islândia', 'name_en' => 'Iceland', 'mask' => '000 0000'],
            ['iso' => 'IQ', 'dial' => '964', 'name_pt' => 'Iraque', 'name_en' => 'Iraq', 'mask' => '000 000 0000'],
            ['iso' => 'IR', 'dial' => '98', 'name_pt' => 'Irã', 'name_en' => 'Iran', 'mask' => '000 000 0000'],
            ['iso' => 'JM', 'dial' => '1', 'name_pt' => 'Jamaica', 'name_en' => 'Jamaica', 'mask' => '(000) 000-0000'],
            ['iso' => 'JO', 'dial' => '962', 'name_pt' => 'Jordânia', 'name_en' => 'Jordan', 'mask' => '0 0000 0000'],
            ['iso' => 'KZ', 'dial' => '7', 'name_pt' => 'Cazaquistão', 'name_en' => 'Kazakhstan', 'mask' => '000 000 00 00'],
            ['iso' => 'KE', 'dial' => '254', 'name_pt' => 'Quênia', 'name_en' => 'Kenya', 'mask' => '000 000000'],
            ['iso' => 'KW', 'dial' => '965', 'name_pt' => 'Kuwait', 'name_en' => 'Kuwait', 'mask' => '0000 0000'],
            ['iso' => 'LV', 'dial' => '371', 'name_pt' => 'Letónia', 'name_en' => 'Latvia', 'mask' => '00 000 000'],
            ['iso' => 'LB', 'dial' => '961', 'name_pt' => 'Líbano', 'name_en' => 'Lebanon', 'mask' => '00 000 000'],
            ['iso' => 'LT', 'dial' => '370', 'name_pt' => 'Lituânia', 'name_en' => 'Lithuania', 'mask' => '000 00000'],
            ['iso' => 'LU', 'dial' => '352', 'name_pt' => 'Luxemburgo', 'name_en' => 'Luxembourg', 'mask' => '000 000 000'],
            ['iso' => 'MO', 'dial' => '853', 'name_pt' => 'Macau', 'name_en' => 'Macao', 'mask' => '0000 0000'],
            ['iso' => 'MK', 'dial' => '389', 'name_pt' => 'Macedônia do Norte', 'name_en' => 'North Macedonia', 'mask' => '00 000 000'],
            ['iso' => 'MG', 'dial' => '261', 'name_pt' => 'Madagascar', 'name_en' => 'Madagascar', 'mask' => '00 00 000 00'],
            ['iso' => 'MT', 'dial' => '356', 'name_pt' => 'Malta', 'name_en' => 'Malta', 'mask' => '0000 0000'],
            ['iso' => 'MU', 'dial' => '230', 'name_pt' => 'Maurício', 'name_en' => 'Mauritius', 'mask' => '0000 0000'],
            ['iso' => 'MD', 'dial' => '373', 'name_pt' => 'Moldávia', 'name_en' => 'Moldova', 'mask' => '0000 0000'],
            ['iso' => 'MC', 'dial' => '377', 'name_pt' => 'Mónaco', 'name_en' => 'Monaco', 'mask' => '00 00 00 00'],
            ['iso' => 'MN', 'dial' => '976', 'name_pt' => 'Mongólia', 'name_en' => 'Mongolia', 'mask' => '0000 0000'],
            ['iso' => 'ME', 'dial' => '382', 'name_pt' => 'Montenegro', 'name_en' => 'Montenegro', 'mask' => '00 000 000'],
            ['iso' => 'MA', 'dial' => '212', 'name_pt' => 'Marrocos', 'name_en' => 'Morocco', 'mask' => '000 000000'],
            ['iso' => 'MZ', 'dial' => '258', 'name_pt' => 'Moçambique', 'name_en' => 'Mozambique', 'mask' => '00 000 0000'],
            ['iso' => 'NA', 'dial' => '264', 'name_pt' => 'Namíbia', 'name_en' => 'Namibia', 'mask' => '00 000 0000'],
            ['iso' => 'NP', 'dial' => '977', 'name_pt' => 'Nepal', 'name_en' => 'Nepal', 'mask' => '000 0000000'],
            ['iso' => 'OM', 'dial' => '968', 'name_pt' => 'Omã', 'name_en' => 'Oman', 'mask' => '0000 0000'],
            ['iso' => 'PK', 'dial' => '92', 'name_pt' => 'Paquistão', 'name_en' => 'Pakistan', 'mask' => '000 0000000'],
            ['iso' => 'PS', 'dial' => '970', 'name_pt' => 'Palestina', 'name_en' => 'Palestine', 'mask' => '000 000 000'],
            ['iso' => 'PR', 'dial' => '1', 'name_pt' => 'Porto Rico', 'name_en' => 'Puerto Rico', 'mask' => '(000) 000-0000'],
            ['iso' => 'QA', 'dial' => '974', 'name_pt' => 'Catar', 'name_en' => 'Qatar', 'mask' => '0000 0000'],
            ['iso' => 'RO', 'dial' => '40', 'name_pt' => 'Romênia', 'name_en' => 'Romania', 'mask' => '000 000 000'],
            ['iso' => 'RW', 'dial' => '250', 'name_pt' => 'Ruanda', 'name_en' => 'Rwanda', 'mask' => '000 000 000'],
            ['iso' => 'SN', 'dial' => '221', 'name_pt' => 'Senegal', 'name_en' => 'Senegal', 'mask' => '00 000 00 00'],
            ['iso' => 'RS', 'dial' => '381', 'name_pt' => 'Sérvia', 'name_en' => 'Serbia', 'mask' => '00 0000000'],
            ['iso' => 'SK', 'dial' => '421', 'name_pt' => 'Eslováquia', 'name_en' => 'Slovakia', 'mask' => '000 000 000'],
            ['iso' => 'SI', 'dial' => '386', 'name_pt' => 'Eslovénia', 'name_en' => 'Slovenia', 'mask' => '00 000 000'],
            ['iso' => 'LK', 'dial' => '94', 'name_pt' => 'Sri Lanka', 'name_en' => 'Sri Lanka', 'mask' => '00 000 0000'],
            ['iso' => 'TW', 'dial' => '886', 'name_pt' => 'Taiwan', 'name_en' => 'Taiwan', 'mask' => '0000 000 000'],
            ['iso' => 'TZ', 'dial' => '255', 'name_pt' => 'Tanzânia', 'name_en' => 'Tanzania', 'mask' => '000 000 000'],
            ['iso' => 'TT', 'dial' => '1', 'name_pt' => 'Trinidad e Tobago', 'name_en' => 'Trinidad and Tobago', 'mask' => '(000) 000-0000'],
            ['iso' => 'TN', 'dial' => '216', 'name_pt' => 'Tunísia', 'name_en' => 'Tunisia', 'mask' => '00 000 000'],
            ['iso' => 'UG', 'dial' => '256', 'name_pt' => 'Uganda', 'name_en' => 'Uganda', 'mask' => '000 000000'],
            ['iso' => 'UZ', 'dial' => '998', 'name_pt' => 'Uzbequistão', 'name_en' => 'Uzbekistan', 'mask' => '00 000 00 00'],
            ['iso' => 'VA', 'dial' => '39', 'name_pt' => 'Vaticano', 'name_en' => 'Vatican City', 'mask' => '00 0000 0000'],
            ['iso' => 'ZM', 'dial' => '260', 'name_pt' => 'Zâmbia', 'name_en' => 'Zambia', 'mask' => '00 0000000'],
            ['iso' => 'ZW', 'dial' => '263', 'name_pt' => 'Zimbábue', 'name_en' => 'Zimbabwe', 'mask' => '00 000 0000'],
        ];
    }

    public static function flag(string $iso): string
    {
        $iso = strtoupper($iso);
        if (strlen($iso) !== 2 || !ctype_alpha($iso)) {
            return '🏳️';
        }
        return mb_chr(127397 + ord($iso[0])) . mb_chr(127397 + ord($iso[1]));
    }

    public static function isValid(string $iso): bool
    {
        foreach (self::all() as $c) {
            if ($c['iso'] === $iso) {
                return true;
            }
        }
        return false;
    }

    public static function dialFor(string $iso): ?string
    {
        foreach (self::all() as $c) {
            if ($c['iso'] === $iso) {
                return $c['dial'];
            }
        }
        return null;
    }

    public static function find(string $iso): ?array
    {
        foreach (self::all() as $c) {
            if ($c['iso'] === $iso) {
                return $c;
            }
        }
        return null;
    }

    public static function defaultIso(): string
    {
        $lang = Lang::locale();
        return str_starts_with($lang, 'pt') ? 'BR' : 'US';
    }

    public static function compose(string $iso, string $national): ?string
    {
        $dial = self::dialFor($iso);
        if ($dial === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $national) ?? '';
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, $dial) && strlen($digits) > strlen($dial) + 6) {
            $full = $digits;
        } else {
            $digits = ltrim($digits, '0');
            if ($digits === '') {
                return null;
            }
            $full = $dial . $digits;
        }
        if (strlen($full) < 10 || strlen($full) > 15) {
            return null;
        }
        return $full;
    }

    /** @return list<array{iso:string,dial:string,label:string,flag:string,mask:string,name:string}> */
    public static function choices(): array
    {
        $pt = str_starts_with(Lang::locale(), 'pt');
        $out = [];
        foreach (self::all() as $c) {
            $name = $pt ? $c['name_pt'] : $c['name_en'];
            $out[] = [
                'iso' => $c['iso'],
                'dial' => $c['dial'],
                'name' => $name,
                'label' => $name . ' (+' . $c['dial'] . ')',
                'flag' => self::flag($c['iso']),
                'mask' => $c['mask'],
            ];
        }
        usort($out, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        $pinned = ['BR', 'US', 'PT'];
        $top = [];
        $rest = [];
        foreach ($out as $row) {
            if (in_array($row['iso'], $pinned, true)) {
                $top[$row['iso']] = $row;
            } else {
                $rest[] = $row;
            }
        }
        $ordered = [];
        foreach ($pinned as $iso) {
            if (isset($top[$iso])) {
                $ordered[] = $top[$iso];
            }
        }
        return array_merge($ordered, $rest);
    }
}