{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "1.0.0";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-uX1YZW8AvLzq3P+xmJQhJ5g3uCUyATv+yCOhjNhiBTk=";
}
