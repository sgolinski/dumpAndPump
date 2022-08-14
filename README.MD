**Install:**

Documentation:

**Warstwa interfejsu użytkownika (lub prezentacji)**

Odpowiedzialna za wyświetlanie informacji dla użytkownika oraz interpretację jego poleceń. Zamiast użytkownika
zewnętrzny aktor może być niekiedy innym systemem komputerowym.

**Application**

Definiuje zamierzone zadania programu i steruje obiektami dziedziny w celu rozwiązania postawionych przed nimi zadań.
Zadania, za które jest odpowiedzialna ta warstwa, są znaczące dla biznesu i wymagane w celu poprawnego współdziałania z
warstwami aplikacji innych systemów. Warstwa jest utrzymywana w zwięzłej postaci. Nie zawiera reguł ani wiedzy
biznesowej, a jedynie zarządza zadaniami i deleguje je do rozwiązania przez obiekty dziedzinowe w kolejnej warstwie
niżej. Warstwa nie musi odzwierciedlać konkretnej sytuacji biznesowej, lecz może posiadać stan, ukazujący postęp zadania
użytkownikowi lub programowi.

**Domain**

Warstwa odpowiedzialna za reprezentację zagadnień biznesowych, informacji o sytuacji biznesowej oraz reguł biznesowych.
W tym miejscu jest przechowywany oraz używany stan odzwierciedlający sytuację biznesową, chociaż szczegóły techniczne
związane z przechowywaniem tego stanu są oddelegowane do warstwy infrastruktury. Ta warstwa stanowi istotę programu od
strony biznesowej.

**Infrastructure**

Udostępnia podstawowe możliwości techniczne wspierające warstwy wyższe, tj. komunikaty wysyłane do aplikacji,
zachowywanie dziedziny, rysowanie elementów interfejsu użytkownika itp. Warstwa infrastruktury może również poprzez
szkielet architektury wspierać wzorzec interakcji pomiędzy wszystkimi czterema warstwami.

**__**IMPORTANT**__**

Oczywiście, istnieją inne style łączenia interfejsu użytkownika z aplikacją. Do naszych zastosowań wszystkie z nich będą
poprawne, o ile będą zachowywać izolację warstwy dziedziny, pozwalając projektować obiekty dziedziny bez jednoczesnego
przejmowania się interfejsem użytkownika, który mógłby ich później używać.

Warstwa infrastruktury nie inicjuje zazwyczaj żadnych akcji w warstwie dziedziny. Istniejąc „poniżej” warstwy dziedziny,
nie powinna ona posiadać żadnej konkretnej wiedzy o dziedzinie, której służy. Rzeczywiście tego rodzaju techniczne
możliwości są bardzo często udostępniane w charakterze USŁUG. Jeżeli na przykład aplikacja chce wysłać wiadomość e-mail,
wtedy w warstwie infrastruktury może zostać zlokalizowany interfejs służący do wysyłania wiadomości, zaś elementy
warstwy aplikacji mogłyby tylko zażądać jej wysłania. Tego rodzaju rozdzielenie daje dodatkową wielofunkcyjność.
Interfejs do wysyłania wiadomości mógłby zostać połączony z serwerem pocztowym, faksowym lub dowolnym innym aktualnie
dostępnym. Jednak najważniejsza korzyść związana jest z uproszczeniem warstwy aplikacji, która jest wąsko skoncentrowana
na swoim zadaniu i wie, kiedy ma wysłać wiadomość, lecz nie przejmuje się tym, w jaki sposób to wykonać.

Warstwy aplikacji oraz dziedziny polegają na USŁUGACH dostarczanych przez warstwę infrastruktury. Jeżeli tylko zakres
USŁUGI został poprawnie wybrany, a jej interfejs prawidłowo zaprojektowany, wtedy obiekt wywołujący może pozostać luźno
związany, bez uwzględniania skomplikowanej logiki kryjącej się pod interfejsem USŁUGI.

Jednak nie cała infrastruktura przykryta jest USŁUGAMI możliwymi do wywołania z warstw wyższych. Niektóre komponenty
techniczne są zaprojektowane tak, aby w bezpośredni sposób wspierać podstawowe funkcje innych warstw (na przykład
poprzez udostępnienie abstrakcyjnych klas bazowych dla obiektów dziedziny) oraz dostarczać mechanizm do ich powiązania (
na przykład implementacje wzorca MVC i podobnych). Tego rodzaju „szkielet architektury” ma zdecydowanie większy wpływ na
projekt innych części programu.
