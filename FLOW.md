Skad wiemy kiedy dana instrukcja sie konczy? Można powiedzieć, że to wyznacza autor scryptu. Jest za dużo możliwości, żeby stwierdzić bez pomocy autora kiedy linijka powinna się skończyć.

Tak więc zróbmy to co powinniśmy na początku, podzielmy to na pojedyńcze instrukcje/linie. Instrukcja kończy się na '\n' albo ';' chyba że ostatnim bądx pierwszym jej wyrazem jest `łącznik`.

Łącznikami są wszystkie zajęte wyrazy oraz znaki specjalne.
Niestety sytuacja nie jest tak łątwa jak się wydaje i instrukcje nie są tylko linijkowe ale też są blokowe. Np funkcje, klasy czy po prostu Ify. Tak więc mamy `instrukcje` i `bloki`. Block składa się, jak opowieść, z wprowadzenia, rozwinięcia i zakończenia. Wprowadzeniem jest typ bloku np `if(true){`, rozwinięciem są zawarte w jego środku instrukcje i bloki, a zakończeniem może być po prostu `}` albo w bardziej niestandardowych przypadkach `} while (true)`;

Tak więc by zdefiniować blok potrzebujemy:
- jak się rozpoczyna
- jak się kończy

W javascript bloki rozpoczynamy na dwa sposoby:
- {
- (

```js
this.pos['func'](() => {
    // instruction
});
```

Tak więc script będzie wiedział że gdy znajdzie '{' lub '(' to ma rozpocząć nowy blok. Problem jest, że w niektórych wypadkach to to nie działa:
```js
let a = {
    b : 'c'
}
```
to nie jest blok tylko instrukcja. Tak więc potrzeby jest dodatkowy check... ehh chyba wole już zdefiniować każdy blok osobno i miec pewność, że dobre są wybrane:
- `if/s|e/(/any/)/s|e/{`
- `for/s|e/(/any/)/s|e/{`
- `while/s|e/(/any/)/s|e/{`
- `do/s|e/{`
- `{`
- `/name//s|e/:/s|e/{`

Here we can see blocks definitions, inside them are data definitions: `/name/` or `/s|e/`. Those are shortcodes for possible data between one landmark (`(` or `for`) and another. Some are already defined like `/s|e/` which translates to `any whitespace or none`, other are user defined like `/name/` which defines any valid name in js. This way when defining `if` block we can be certain that all variants are includes:
- `if(true){`
- `if (true) {`
- `if(true) {`
- `if (true){`
- `if (true)
  {`    
- `if
  (true){`

etc.
