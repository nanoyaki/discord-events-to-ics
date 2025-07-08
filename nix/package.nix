{
  lib,
  php84,
}:

php84.buildComposerProject2 {
  pname = "discord-events-to-ics";
  version = "1.1.2";

  src = lib.cleanSource ./..;

  composerNoPlugins = false;
  composerLock = ../composer.lock;
  vendorHash = "sha256-N6g/E/+5MF1AZo8qO/vqNXKIUJEKh0+2KedVvnu3LIk=";

  meta.broken = true;
}
