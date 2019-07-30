
var casper = require('casper').create({
  viewportSize: { width: 1280, height: 800 },
  loadImages: true,
  loadPlugins: true,
});
// var url = 'https://www.bnisantaka.com/';
var url = 'https://www.bniconnectglobal.com/web/secure/reportsHome';

// casper.userAgent('');
var html = "";
var system = require("system");
var args = system.args;
var name = args[4];
var psw = args[5];
var id = args[6];
casper.start(url, function () {
  this.echo("started");
  this.echo("1/4");
  casper.wait(10000, function () {
    this.fillSelectors('form.hJcysH', {
      'input[name="username"]': name,
      'input[name="password"]': psw,
    }, true);
  });
  // casper.then(function(){
  //   casper.wait(5000, function () {
  //     this.click('input[value="pateikti"]');
  //   })
  // })
  casper.then(function () {
    this.echo("2/4");
    casper.wait(10000, function () {
      if (this.click('a[href="#ui-tabs-2"]'))
        casper.echo("Prisijungiau");
      else casper.echo("Neprisijungiau");
    })
  })

  casper.then(function () {
    this.echo("3/4");
    casper.wait(10000, function () {
      // this.click('input[value="Pateikti"]');
      this.fillSelectors('form#chapter_Roster_Report', {}, true);
    });
  })

  casper.then(function () {
    this.echo("4/4");
    casper.wait(10000, function () {
      // this.capture("t.png");
      this.echo("pic taken")
      this.page.switchToChildFrame(0);
      casper.wait(3000, function () {
        elements = casper.getElementsInfo('#__bookmark_1 tbody td');
      })
      casper.wait(3000, function () {
        this.page.switchToParentFrame();
      })

    })
  })

  // var js = this.evaluate(function() {
  // 	return document; 
  // });	
  // casper.then(function(){
  //   this.echo(js.all[0].outerHTML); 
  // })

});

casper.run(function () {
  var utils = require('utils');
  var fs = require('fs');
  // utils.dump(elements);

  var person = {};
  var people = [];
  elements.forEach(function (element) {
    // utils.dump(element);
    // casper.echo(element);
    if (element["x"] < 930) {
      z = element["text"];
      if (element["x"] == 18) person["name"] = z.substring(2, z.length - 2);
      if (element["x"] == 232) person["business_sector"] = z.substring(1, z.length - 1);
      if (element["x"] == 464) person["company"] = z.substring(1, z.length - 1);
      if (element["x"] == 827) {
        person["phone"] = z.substring(1, z.length - 1);
        person["active"] = "1";
        people.push(JSON.stringify(person));
        // utils.dump(person);
        // fs.writeFile('text.json', '{"name": "Julius", "age": 10}' , 'utf-8');
        // fs.writeFileSync('./data.json', util.inspect(obj) , 'utf-8');
        // casper.echo("--------------------------------------------------------------------------")
      }

    }
  });
  fs.write(id+"members.json", "[", 'w');
  fs.write(id+"members.json", people, 'w+');
  fs.write(id+"members.json", "]", 'w+');
  // utils.dump(people);
  this.echo("Everything done");
  this.exit();
});